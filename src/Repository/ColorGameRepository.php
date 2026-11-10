<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\DexQueryOptions;
use App\Entity\ColorGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ColorGame>
 */
class ColorGameRepository extends ServiceEntityRepository
{
    private const string PLAYABLE_POKEMONS = <<<'SQL'
        SELECT      p.id AS "pokemon_id",
                    STRING_AGG(CAST(pcs.position AS varchar), ',' ORDER BY pcs.position) AS "positions"
        FROM        pokemon AS p
            JOIN    pokemon_color_set AS pcs
                ON  pcs.pokemon_id = p.id
                AND pcs.is_shiny = :is_shiny
        WHERE       p.deleted_at IS NULL
                AND p.id IN (SELECT da.pokemon_id FROM dex_availability AS da WHERE da.dex_id = :dex_id)
        GROUP BY    p.id
        SQL;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ColorGame::class);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getPlayableDexList(DexQueryOptions $options, ?string $locale = null, string $fallbackLocale = 'en'): array
    {
        $where = '';

        if (!$options->includeUnreleasedDex) {
            $where .= ' AND d.is_released = true ';
        }

        if (!$options->includePremiumDex) {
            $where .= ' AND d.is_premium = false ';
        }

        $playablePokemons = str_replace(
            [':dex_id', ':is_shiny'],
            ['d.id', 'd.is_shiny'],
            self::PLAYABLE_POKEMONS,
        );

        $dexColumns = GameLocalizedColumns::playableDex($locale);

        $sql = <<<SQL
            SELECT      d.slug AS "slug",
                        {$dexColumns},
                        d.is_shiny AS "is_shiny",
                        d.is_released AS "is_released",
                        d.is_premium AS "is_premium",
                        (SELECT COUNT(1) FROM ({$playablePokemons}) AS pp) AS "playable_count",
                        dc.slug AS "category_slug",
                        dc.order_number AS "category_order_number"
            FROM        dex AS d
                LEFT JOIN dex_category AS dc
                    ON  dc.id = d.category_id
                    AND dc.deleted_at IS NULL
            WHERE       d.can_be_color_play = true
                    AND d.deleted_at IS NULL
                        {$where}
            ORDER BY    d.game_order_number ASC, d.order_number ASC, d.slug ASC
            SQL;

        /** @var array<array-key, array<string, mixed>> */
        return $this->getConnection()->fetchAllAssociative(
            $sql,
            GameLocalizedColumns::parameters($locale, $fallbackLocale),
        );
    }

    /**
     * @return array<string, array<string, mixed>> keyed by dex slug
     */
    public function getInProgressGamesByDex(string $trainerExternalId, ?string $locale = null, string $fallbackLocale = 'en'): array
    {
        $columns = self::gameColumns($locale);

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        color_game AS cg
                JOIN    dex AS d
                    ON  d.id = cg.dex_id
            WHERE       cg.trainer_external_id = :trainer_external_id
                    AND cg.status = :status
            SQL;

        $rows = $this->getConnection()->fetchAllAssociative(
            $sql,
            [
                'trainer_external_id' => $trainerExternalId,
                'status' => ColorGame::STATUS_IN_PROGRESS,
            ] + GameLocalizedColumns::parameters($locale, $fallbackLocale),
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
     * @return null|array{id: string, is_shiny: bool}
     */
    public function getPlayableDex(string $dexSlug): ?array
    {
        $sql = <<<'SQL'
            SELECT      d.id AS "id",
                        d.is_shiny AS "is_shiny"
            FROM        dex AS d
            WHERE       d.slug = :dex_slug
                    AND d.can_be_color_play = true
                    AND d.deleted_at IS NULL
            SQL;

        /** @var array{id: string, is_shiny: bool}|false $row */
        $row = $this->getConnection()->fetchAssociative($sql, ['dex_slug' => $dexSlug]);

        return false === $row ? null : $row;
    }

    /**
     * @return list<array{pokemon_id: string, positions: string}>
     */
    public function getPlayablePokemons(string $dexId, bool $isShiny): array
    {
        /** @var list<array{pokemon_id: string, positions: string}> */
        return $this->getConnection()->fetchAllAssociative(
            self::PLAYABLE_POKEMONS.' ORDER BY p.id',
            [
                'dex_id' => $dexId,
                'is_shiny' => $isShiny,
            ],
            [
                'dex_id' => ParameterType::STRING,
                'is_shiny' => ParameterType::BOOLEAN,
            ],
        );
    }

    /**
     * Starts a new game, abandoning the one in progress for the same dex if any.
     *
     * @param list<array{pokemon_id: string, choices_order: list<int>}> $rounds in play order
     */
    public function startGame(
        string $gameId,
        string $trainerExternalId,
        string $dexId,
        bool $isShiny,
        array $rounds,
    ): void {
        $this->getConnection()->transactional(function () use ($gameId, $trainerExternalId, $dexId, $isShiny, $rounds): void {
            $this->abandonInProgressGames($trainerExternalId, $dexId);
            $this->insertGame($gameId, $trainerExternalId, $dexId, $isShiny, $rounds);
        });
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getGame(string $trainerExternalId, string $gameId, ?string $locale = null, string $fallbackLocale = 'en'): ?array
    {
        $columns = self::gameColumns($locale);

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        color_game AS cg
                JOIN    dex AS d
                    ON  d.id = cg.dex_id
            WHERE       cg.id = :id
                    AND cg.trainer_external_id = :trainer_external_id
            SQL;

        $row = $this->getConnection()->fetchAssociative(
            $sql,
            [
                'id' => $gameId,
                'trainer_external_id' => $trainerExternalId,
            ] + GameLocalizedColumns::parameters($locale, $fallbackLocale),
        );

        return false === $row ? null : $row;
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getGames(string $trainerExternalId, ?string $locale = null, string $fallbackLocale = 'en'): array
    {
        $columns = self::gameColumns($locale);

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        color_game AS cg
                JOIN    dex AS d
                    ON  d.id = cg.dex_id
            WHERE       cg.trainer_external_id = :trainer_external_id
            ORDER BY    cg.started_at DESC, cg.id ASC
            SQL;

        return $this->getConnection()->fetchAllAssociative(
            $sql,
            ['trainer_external_id' => $trainerExternalId] + GameLocalizedColumns::parameters($locale, $fallbackLocale),
        );
    }

    /**
     * The next round is the first one not answered yet.
     *
     * @return null|array<string, mixed>
     */
    public function getNextRound(string $gameId, ?string $locale = null, string $fallbackLocale = 'en'): ?array
    {
        return $this->getRound($gameId, null, $locale, $fallbackLocale);
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getRoundAt(string $gameId, int $position, ?string $locale = null, string $fallbackLocale = 'en'): ?array
    {
        return $this->getRound($gameId, $position, $locale, $fallbackLocale);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getColorSets(string $pokemonId, bool $isShiny, ?string $locale = null, string $fallbackLocale = 'en'): array
    {
        $names = GameLocalizedColumns::colorSet($locale);

        $sql = <<<SQL
            SELECT      pcs.position AS "position",
                        pcs.codes AS "codes",
                        {$names}
            FROM        pokemon_color_set AS pcs
            WHERE       pcs.pokemon_id = :pokemon_id
                    AND pcs.is_shiny = :is_shiny
            ORDER BY    pcs.position ASC
            SQL;

        return $this->getConnection()->fetchAllAssociative(
            $sql,
            [
                'pokemon_id' => $pokemonId,
                'is_shiny' => $isShiny,
            ] + GameLocalizedColumns::parameters($locale, $fallbackLocale),
            [
                'pokemon_id' => ParameterType::STRING,
                'is_shiny' => ParameterType::BOOLEAN,
            ],
        );
    }

    /**
     * The answered rounds, in play order: the ones not answered yet would give the answers away.
     *
     * @return array<array-key, array<string, mixed>>
     */
    public function getAnsweredRounds(string $gameId, ?string $locale = null, string $fallbackLocale = 'en'): array
    {
        $columns = self::roundColumns($locale);

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        color_game_round AS cgr
                JOIN    pokemon AS p
                    ON  p.id = cgr.pokemon_id
            WHERE       cgr.color_game_id = :color_game_id
                    AND cgr.chosen_choice IS NOT NULL
            ORDER BY    cgr.position ASC
            SQL;

        return $this->getConnection()->fetchAllAssociative(
            $sql,
            ['color_game_id' => $gameId] + GameLocalizedColumns::parameters($locale, $fallbackLocale),
        );
    }

    /**
     * The colour sets of the answered rounds, with the position of their round.
     *
     * @return array<array-key, array<string, mixed>>
     */
    public function getAnsweredRoundsColorSets(string $gameId, ?string $locale = null, string $fallbackLocale = 'en'): array
    {
        $names = GameLocalizedColumns::colorSet($locale);

        $sql = <<<SQL
            SELECT      cgr.position AS "round_position",
                        pcs.position AS "position",
                        pcs.codes AS "codes",
                        {$names}
            FROM        color_game_round AS cgr
                JOIN    color_game AS cg
                    ON  cg.id = cgr.color_game_id
                JOIN    pokemon_color_set AS pcs
                    ON  pcs.pokemon_id = cgr.pokemon_id
                    AND pcs.is_shiny = cg.is_shiny
            WHERE       cgr.color_game_id = :color_game_id
                    AND cgr.chosen_choice IS NOT NULL
            ORDER BY    cgr.position ASC, pcs.position ASC
            SQL;

        return $this->getConnection()->fetchAllAssociative(
            $sql,
            ['color_game_id' => $gameId] + GameLocalizedColumns::parameters($locale, $fallbackLocale),
        );
    }

    /**
     * Saves the answer of a round, only if it has not been answered yet.
     *
     * @return bool true if the answer has been saved
     */
    public function answerRound(string $gameId, int $position, int $chosenChoice, bool $isCorrect): bool
    {
        $connection = $this->getConnection();

        return $connection->transactional(static function (Connection $connection) use ($gameId, $position, $chosenChoice, $isCorrect): bool {
            $answered = $connection->executeStatement(
                <<<'SQL'
                    UPDATE  color_game_round
                    SET     chosen_choice = :chosen_choice,
                            is_correct = :is_correct,
                            answered_at = NOW()
                    WHERE   color_game_id = :color_game_id
                        AND position = :position
                        AND chosen_choice IS NULL
                    SQL,
                [
                    'chosen_choice' => $chosenChoice,
                    'is_correct' => $isCorrect,
                    'color_game_id' => $gameId,
                    'position' => $position,
                ],
                [
                    'chosen_choice' => ParameterType::INTEGER,
                    'is_correct' => ParameterType::BOOLEAN,
                    'color_game_id' => ParameterType::STRING,
                    'position' => ParameterType::INTEGER,
                ],
            );

            if (0 === $answered) {
                return false;
            }

            $connection->executeStatement(
                <<<'SQL'
                    UPDATE  color_game
                    SET     answered_count = answered_count + 1,
                            correct_count = correct_count + CASE WHEN :is_correct THEN 1 ELSE 0 END,
                            last_played_at = NOW(),
                            status = CASE WHEN answered_count + 1 >= round_count THEN :completed ELSE status END,
                            ended_at = CASE WHEN answered_count + 1 >= round_count THEN NOW() ELSE ended_at END
                    WHERE   id = :id
                    SQL,
                [
                    'is_correct' => $isCorrect,
                    'completed' => ColorGame::STATUS_COMPLETED,
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
            UPDATE  color_game
            SET     status = :abandoned,
                    ended_at = NOW()
            WHERE   trainer_external_id = :trainer_external_id
                AND dex_id = :dex_id
                AND status = :in_progress
            SQL;

        $this->getConnection()->executeStatement(
            $sql,
            [
                'abandoned' => ColorGame::STATUS_ABANDONED,
                'in_progress' => ColorGame::STATUS_IN_PROGRESS,
                'trainer_external_id' => $trainerExternalId,
                'dex_id' => $dexId,
            ],
        );
    }

    /**
     * @param list<array{pokemon_id: string, choices_order: list<int>}> $rounds in play order
     */
    private function insertGame(
        string $gameId,
        string $trainerExternalId,
        string $dexId,
        bool $isShiny,
        array $rounds,
    ): void {
        $connection = $this->getConnection();

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO color_game (
                    id,
                    trainer_external_id,
                    dex_id,
                    is_shiny,
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
                    :is_shiny,
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
                'is_shiny' => $isShiny,
                'status' => ColorGame::STATUS_IN_PROGRESS,
                'round_count' => count($rounds),
            ],
            [
                'is_shiny' => ParameterType::BOOLEAN,
                'round_count' => ParameterType::INTEGER,
            ],
        );

        $pokemonIds = array_map(
            static fn (array $round): string => $round['pokemon_id'],
            $rounds,
        );
        $choicesOrders = array_map(
            static fn (array $round): string => '"'.implode(',', $round['choices_order']).'"',
            $rounds,
        );

        $connection->executeStatement(
            <<<'SQL'
                INSERT INTO color_game_round (id, color_game_id, pokemon_id, position, choices_order)
                SELECT      gen_random_uuid(),
                            :color_game_id,
                            r.pokemon_id,
                            r.position,
                            r.choices_order
                FROM        UNNEST(
                                CAST(:pokemon_ids AS uuid[]),
                                CAST(:choices_orders AS text[])
                            ) WITH ORDINALITY AS r(pokemon_id, choices_order, position)
                SQL,
            [
                'color_game_id' => $gameId,
                'pokemon_ids' => '{'.implode(',', $pokemonIds).'}',
                'choices_orders' => '{'.implode(',', $choicesOrders).'}',
            ],
        );
    }

    /**
     * @return null|array<string, mixed>
     */
    private function getRound(string $gameId, ?int $position, ?string $locale, string $fallbackLocale): ?array
    {
        $columns = self::roundColumns($locale);
        $where = null === $position ? 'cgr.chosen_choice IS NULL' : 'cgr.position = :position';

        $sql = <<<SQL
            SELECT      {$columns}
            FROM        color_game_round AS cgr
                JOIN    pokemon AS p
                    ON  p.id = cgr.pokemon_id
            WHERE       cgr.color_game_id = :color_game_id
                    AND {$where}
            ORDER BY    cgr.position ASC
            LIMIT       1
            SQL;

        $params = ['color_game_id' => $gameId] + GameLocalizedColumns::parameters($locale, $fallbackLocale);

        if (null !== $position) {
            $params['position'] = $position;
        }

        $row = $this->getConnection()->fetchAssociative($sql, $params);

        return false === $row ? null : $row;
    }

    private static function gameColumns(?string $locale): string
    {
        $dex = GameLocalizedColumns::gameDex($locale);

        return <<<SQL
            cg.id AS "id",
            d.slug AS "dex_slug",
            {$dex},
            cg.is_shiny AS "is_shiny",
            cg.status AS "status",
            cg.round_count AS "round_count",
            cg.answered_count AS "answered_count",
            cg.correct_count AS "correct_count",
            (
                SELECT  STRING_AGG(CASE WHEN r.is_correct THEN '1' ELSE '0' END, '' ORDER BY r.position)
                FROM    color_game_round AS r
                WHERE   r.color_game_id = cg.id
                    AND r.is_correct IS NOT NULL
            ) AS "results",
            cg.started_at AS "started_at",
            cg.last_played_at AS "last_played_at",
            cg.ended_at AS "ended_at"
            SQL;
    }

    private static function roundColumns(?string $locale): string
    {
        $pokemon = GameLocalizedColumns::pokemon($locale);

        return <<<SQL
            cgr.position AS "position",
            cgr.choices_order AS "choices_order",
            cgr.chosen_choice AS "chosen_choice",
            cgr.is_correct AS "is_correct",
            p.id AS "pokemon_id",
            p.slug AS "pokemon_slug",
            {$pokemon},
            p.national_dex_number AS "pokemon_national_dex_number",
            p.icon_name AS "pokemon_icon"
            SQL;
    }

    private function getConnection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
