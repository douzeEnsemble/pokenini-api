<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DexQueryOptions;
use App\Entity\WhichShinyGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WhichShinyGame>
 */
class WhichShinyGameRepository extends ServiceEntityRepository
{
    private const string GAME_COLUMNS = <<<'SQL'
        sg.id AS "id",
        d.slug AS "dex_slug",
        d.name AS "dex_name",
        d.french_name AS "dex_french_name",
        sg.status AS "status",
        sg.round_count AS "round_count",
        sg.answered_count AS "answered_count",
        sg.correct_count AS "correct_count",
        (
            SELECT  STRING_AGG(CASE WHEN r.is_correct THEN '1' ELSE '0' END, '' ORDER BY r.position)
            FROM    which_shiny_game_round AS r
            WHERE   r.which_shiny_game_id = sg.id
                AND r.is_correct IS NOT NULL
        ) AS "results",
        sg.started_at AS "started_at",
        sg.last_played_at AS "last_played_at",
        sg.ended_at AS "ended_at"
        SQL;

    /** The dex of the colour game showing regular Pokémons */
    private const string PLAYABLE_DEX = <<<'SQL'
        d.can_be_color_play = true
        AND d.is_shiny = false
        AND d.deleted_at IS NULL
        SQL;

    private const string PLAYABLE_POKEMONS = <<<'SQL'
        SELECT      p.id AS "pokemon_id"
        FROM        pokemon AS p
        WHERE       p.deleted_at IS NULL
                AND p.id IN (SELECT da.pokemon_id FROM dex_availability AS da WHERE da.dex_id = :dex_id)
        SQL;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WhichShinyGame::class);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getPlayableDexList(DexQueryOptions $options): array
    {
        $where = '';

        if (!$options->includeUnreleasedDex) {
            $where .= ' AND d.is_released = true ';
        }

        if (!$options->includePremiumDex) {
            $where .= ' AND d.is_premium = false ';
        }

        $playableDex = self::PLAYABLE_DEX;
        $playablePokemons = str_replace(':dex_id', 'd.id', self::PLAYABLE_POKEMONS);

        $sql = <<<SQL
            SELECT      d.slug AS "slug",
                        d.name AS "name",
                        d.french_name AS "french_name",
                        d.is_released AS "is_released",
                        d.is_premium AS "is_premium",
                        d.description AS "description",
                        d.french_description AS "french_description",
                        (SELECT COUNT(1) FROM ({$playablePokemons}) AS pp) AS "playable_count",
                        dc.slug AS "category_slug",
                        dc.name AS "category_name",
                        dc.french_name AS "category_french_name",
                        dc.order_number AS "category_order_number"
            FROM        dex AS d
                LEFT JOIN dex_category AS dc
                    ON  dc.id = d.category_id
                    AND dc.deleted_at IS NULL
            WHERE       {$playableDex}
                        {$where}
            ORDER BY    d.game_order_number ASC, d.order_number ASC, d.slug ASC
            SQL;

        /** @var array<array-key, array<string, mixed>> */
        return $this->getConnection()->fetchAllAssociative($sql);
    }

    /**
     * @return array<string, array<string, mixed>> keyed by dex slug
     */
    public function getInProgressGamesByDex(string $trainerExternalId): array
    {
        $columns = self::GAME_COLUMNS;

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        which_shiny_game AS sg
                JOIN    dex AS d
                    ON  d.id = sg.dex_id
            WHERE       sg.trainer_external_id = :trainer_external_id
                    AND sg.status = :status
            SQL;

        $rows = $this->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'status' => WhichShinyGame::STATUS_IN_PROGRESS,
            ],
        );

        $games = [];
        foreach ($rows as $row) {
            /** @var scalar $dexSlug */
            $dexSlug = $row['dex_slug'];

            $games[(string) $dexSlug] = $row;
        }

        return $games;
    }

    /**
     * @return null|string the dex id
     */
    public function getPlayableDexId(string $dexSlug): ?string
    {
        $playableDex = self::PLAYABLE_DEX;

        $sql = <<<SQL
            SELECT      d.id
            FROM        dex AS d
            WHERE       d.slug = :dex_slug
                    AND {$playableDex}
            SQL;

        /** @var false|string $dexId */
        $dexId = $this->getConnection()->fetchOne($sql, ['dex_slug' => $dexSlug]);

        return false === $dexId ? null : $dexId;
    }

    /**
     * @return list<string>
     */
    public function getPlayablePokemonIds(string $dexId): array
    {
        /** @var list<string> */
        return $this->getConnection()->fetchFirstColumn(
            self::PLAYABLE_POKEMONS.' ORDER BY p.id',
            ['dex_id' => $dexId],
            ['dex_id' => ParameterType::STRING],
        );
    }

    /**
     * Starts a new game, abandoning the one in progress for the same dex if any.
     *
     * @param list<array{pokemon_id: string, shiny_slot: int}> $rounds in play order
     */
    public function startGame(
        string $gameId,
        string $trainerExternalId,
        string $dexId,
        array $rounds,
    ): void {
        $this->getConnection()->transactional(function () use ($gameId, $trainerExternalId, $dexId, $rounds): void {
            $this->abandonInProgressGames($trainerExternalId, $dexId);
            $this->insertGame($gameId, $trainerExternalId, $dexId, $rounds);
        });
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getGame(string $trainerExternalId, string $gameId): ?array
    {
        $columns = self::GAME_COLUMNS;

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        which_shiny_game AS sg
                JOIN    dex AS d
                    ON  d.id = sg.dex_id
            WHERE       sg.id = :id
                    AND sg.trainer_external_id = :trainer_external_id
            SQL;

        $row = $this->getConnection()->fetchAssociative(
            $sql,
            [
                'id' => $gameId,
                'trainer_external_id' => $trainerExternalId,
            ],
        );

        return false === $row ? null : $row;
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getGames(string $trainerExternalId): array
    {
        $columns = self::GAME_COLUMNS;

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        which_shiny_game AS sg
                JOIN    dex AS d
                    ON  d.id = sg.dex_id
            WHERE       sg.trainer_external_id = :trainer_external_id
            ORDER BY    sg.started_at DESC, sg.id ASC
            SQL;

        return $this->getConnection()->fetchAllAssociative(
            $sql,
            ['trainer_external_id' => $trainerExternalId],
        );
    }

    /**
     * The next round is the first one not answered yet.
     *
     * @return null|array<string, mixed>
     */
    public function getNextRound(string $gameId): ?array
    {
        return $this->getRound($gameId, null);
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getRoundAt(string $gameId, int $position): ?array
    {
        return $this->getRound($gameId, $position);
    }

    /**
     * Saves the answer of a round, only if it has not been answered yet.
     *
     * @return bool true if the answer has been saved
     */
    public function answerRound(string $gameId, int $position, int $chosenSlot, bool $isCorrect): bool
    {
        $connection = $this->getConnection();

        return $connection->transactional(static function (Connection $connection) use ($gameId, $position, $chosenSlot, $isCorrect): bool {
            $answered = $connection->executeStatement(
                <<<'SQL'
                    UPDATE  which_shiny_game_round
                    SET     chosen_slot = :chosen_slot,
                            is_correct = :is_correct,
                            answered_at = NOW()
                    WHERE   which_shiny_game_id = :which_shiny_game_id
                        AND position = :position
                        AND chosen_slot IS NULL
                    SQL,
                [
                    'chosen_slot' => $chosenSlot,
                    'is_correct' => $isCorrect,
                    'which_shiny_game_id' => $gameId,
                    'position' => $position,
                ],
                [
                    'chosen_slot' => ParameterType::INTEGER,
                    'is_correct' => ParameterType::BOOLEAN,
                    'which_shiny_game_id' => ParameterType::STRING,
                    'position' => ParameterType::INTEGER,
                ],
            );

            if (0 === $answered) {
                return false;
            }

            $connection->executeStatement(
                <<<'SQL'
                    UPDATE  which_shiny_game
                    SET     answered_count = answered_count + 1,
                            correct_count = correct_count + CASE WHEN :is_correct THEN 1 ELSE 0 END,
                            last_played_at = NOW(),
                            status = CASE WHEN answered_count + 1 >= round_count THEN :completed ELSE status END,
                            ended_at = CASE WHEN answered_count + 1 >= round_count THEN NOW() ELSE ended_at END
                    WHERE   id = :id
                    SQL,
                [
                    'is_correct' => $isCorrect,
                    'completed' => WhichShinyGame::STATUS_COMPLETED,
                    'id' => $gameId,
                ],
                [
                    'is_correct' => ParameterType::BOOLEAN,
                    'completed' => ParameterType::STRING,
                    'id' => ParameterType::STRING,
                ],
            );

            return true;
        });
    }

    private function abandonInProgressGames(string $trainerExternalId, string $dexId): void
    {
        $sql = <<<'SQL'
            UPDATE  which_shiny_game
            SET     status = :abandoned,
                    ended_at = NOW()
            WHERE   trainer_external_id = :trainer_external_id
                AND dex_id = :dex_id
                AND status = :in_progress
            SQL;

        $this->getConnection()->executeStatement(
            $sql,
            [
                'abandoned' => WhichShinyGame::STATUS_ABANDONED,
                'in_progress' => WhichShinyGame::STATUS_IN_PROGRESS,
                'trainer_external_id' => $trainerExternalId,
                'dex_id' => $dexId,
            ],
        );
    }

    /**
     * @param list<array{pokemon_id: string, shiny_slot: int}> $rounds in play order
     */
    private function insertGame(
        string $gameId,
        string $trainerExternalId,
        string $dexId,
        array $rounds,
    ): void {
        $connection = $this->getConnection();

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO which_shiny_game (
                    id,
                    trainer_external_id,
                    dex_id,
                    status,
                    round_count,
                    answered_count,
                    correct_count,
                    started_at
                )
                VALUES (
                    :id,
                    :trainer_external_id,
                    :dex_id,
                    :status,
                    :round_count,
                    0,
                    0,
                    NOW()
                )
                SQL,
            [
                'id' => $gameId,
                'trainer_external_id' => $trainerExternalId,
                'dex_id' => $dexId,
                'status' => WhichShinyGame::STATUS_IN_PROGRESS,
                'round_count' => count($rounds),
            ],
            [
                'round_count' => ParameterType::INTEGER,
            ],
        );

        $pokemonIds = array_map(
            static fn (array $round): string => $round['pokemon_id'],
            $rounds,
        );
        $shinySlots = array_map(
            static fn (array $round): int => $round['shiny_slot'],
            $rounds,
        );

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO which_shiny_game_round (id, which_shiny_game_id, pokemon_id, position, shiny_slot)
                SELECT      gen_random_uuid(),
                            :which_shiny_game_id,
                            r.pokemon_id,
                            r.position,
                            r.shiny_slot
                FROM        UNNEST(
                                CAST(:pokemon_ids AS uuid[]),
                                CAST(:shiny_slots AS integer[])
                            ) WITH ORDINALITY AS r(pokemon_id, shiny_slot, position)
                SQL,
            [
                'which_shiny_game_id' => $gameId,
                'pokemon_ids' => '{'.implode(',', $pokemonIds).'}',
                'shiny_slots' => '{'.implode(',', $shinySlots).'}',
            ],
        );
    }

    /**
     * @return null|array<string, mixed>
     */
    private function getRound(string $gameId, ?int $position): ?array
    {
        $where = null === $position ? 'sgr.chosen_slot IS NULL' : 'sgr.position = :position';

        $sql = <<<SQL
            SELECT      sgr.position AS "position",
                        sgr.shiny_slot AS "shiny_slot",
                        sgr.chosen_slot AS "chosen_slot",
                        sgr.is_correct AS "is_correct",
                        p.id AS "pokemon_id",
                        p.slug AS "pokemon_slug",
                        p.name AS "pokemon_name",
                        p.french_name AS "pokemon_french_name",
                        p.simplified_name AS "pokemon_simplified_name",
                        p.simplified_french_name AS "pokemon_simplified_french_name",
                        p.forms_label AS "pokemon_forms_label",
                        p.forms_french_label AS "pokemon_forms_french_label",
                        p.national_dex_number AS "pokemon_national_dex_number",
                        p.icon_name AS "pokemon_icon"
            FROM        which_shiny_game_round AS sgr
                JOIN    pokemon AS p
                    ON  p.id = sgr.pokemon_id
            WHERE       sgr.which_shiny_game_id = :which_shiny_game_id
                    AND {$where}
            ORDER BY    sgr.position ASC
            LIMIT       1
            SQL;

        $params = ['which_shiny_game_id' => $gameId];

        if (null !== $position) {
            $params['position'] = $position;
        }

        $row = $this->getConnection()->fetchAssociative($sql, $params);

        return false === $row ? null : $row;
    }

    private function getConnection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
