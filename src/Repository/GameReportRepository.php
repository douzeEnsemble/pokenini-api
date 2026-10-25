<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ColorGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Usage statistics of the three games (color_game, shiny_game, which_shiny_game).
 *
 * @extends ServiceEntityRepository<ColorGame>
 */
class GameReportRepository extends ServiceEntityRepository
{
    private const string ALL_GAMES = <<<'SQL'
        SELECT  'color_game' AS "game", g.trainer_external_id, g.dex_id, g.status,
                g.round_count, g.answered_count, g.correct_count, g.started_at
        FROM    color_game AS g
        UNION ALL
        SELECT  'shiny_game' AS "game", g.trainer_external_id, g.dex_id, g.status,
                g.round_count, g.answered_count, g.correct_count, g.started_at
        FROM    shiny_game AS g
        UNION ALL
        SELECT  'which_shiny_game' AS "game", g.trainer_external_id, g.dex_id, g.status,
                g.round_count, g.answered_count, g.correct_count, g.started_at
        FROM    which_shiny_game AS g
        SQL;

    private const string ALL_ANSWERED_ROUNDS = <<<'SQL'
        SELECT  'color_game' AS "game", r.pokemon_id, r.is_correct
        FROM    color_game_round AS r
        WHERE   r.is_correct IS NOT NULL
        UNION ALL
        SELECT  'shiny_game' AS "game", r.pokemon_id, r.is_correct
        FROM    shiny_game_round AS r
        WHERE   r.is_correct IS NOT NULL
        UNION ALL
        SELECT  'which_shiny_game' AS "game", r.pokemon_id, r.is_correct
        FROM    which_shiny_game_round AS r
        WHERE   r.is_correct IS NOT NULL
        SQL;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ColorGame::class);
    }

    /**
     * @return list<array<string, mixed>> one row per game having at least one game
     */
    public function getGameCounts(): array
    {
        $games = self::ALL_GAMES;

        $sql = <<<SQL
            SELECT      g.game AS "game",
                        COUNT(*) AS "games_count",
                        COUNT(*) FILTER (WHERE g.answered_count > 0) AS "played_count",
                        COUNT(*) FILTER (WHERE g.status = 'in_progress') AS "in_progress_count",
                        COUNT(*) FILTER (WHERE g.status = 'completed') AS "completed_count",
                        COUNT(*) FILTER (WHERE g.status = 'abandoned') AS "abandoned_count",
                        COUNT(DISTINCT g.trainer_external_id) AS "players_count",
                        SUM(g.round_count) AS "round_count",
                        SUM(g.answered_count) AS "answered_count",
                        SUM(g.correct_count) AS "correct_count"
            FROM        ({$games}) AS g
            GROUP BY    g.game
            ORDER BY    g.game
            SQL;

        /** @var list<array<string, mixed>> */
        return $this->getConnection()->fetchAllAssociative($sql);
    }

    /**
     * @return list<array<string, mixed>> ordered by game, then most played dex first
     */
    public function getDexUsage(): array
    {
        $games = self::ALL_GAMES;

        $sql = <<<SQL
            SELECT      g.game AS "game",
                        d.slug AS "slug",
                        d.name AS "name",
                        d.french_name AS "french_name",
                        COUNT(*) AS "games_count",
                        COUNT(DISTINCT g.trainer_external_id) AS "players_count",
                        SUM(g.answered_count) AS "answered_count",
                        SUM(g.correct_count) AS "correct_count"
            FROM        ({$games}) AS g
                JOIN    dex AS d
                    ON  d.id = g.dex_id
            GROUP BY    g.game, d.slug, d.name, d.french_name, d.order_number
            ORDER BY    g.game, "games_count" DESC, d.order_number, d.slug
            SQL;

        /** @var list<array<string, mixed>> */
        return $this->getConnection()->fetchAllAssociative($sql);
    }

    /**
     * The Pokémon with the lowest success rate of each game.
     *
     * @return list<array<string, mixed>> ordered by game, then lowest success rate first
     */
    public function getHardestPokemons(int $minAnswers, int $limit): array
    {
        $rounds = self::ALL_ANSWERED_ROUNDS;

        $sql = <<<SQL
            SELECT      s.game AS "game",
                        s.slug AS "slug",
                        s.name AS "name",
                        s.french_name AS "french_name",
                        s.simplified_name AS "simplified_name",
                        s.simplified_french_name AS "simplified_french_name",
                        s.forms_label AS "forms_label",
                        s.forms_french_label AS "forms_french_label",
                        s.icon_name AS "icon",
                        s.answered_count AS "answered_count",
                        s.correct_count AS "correct_count"
            FROM        (
                SELECT      r.game,
                            p.slug, p.name, p.french_name,
                            p.simplified_name, p.simplified_french_name,
                            p.forms_label, p.forms_french_label, p.icon_name,
                            COUNT(*) AS answered_count,
                            COUNT(*) FILTER (WHERE r.is_correct) AS correct_count,
                            ROW_NUMBER() OVER (
                                PARTITION BY r.game
                                ORDER BY    CAST(COUNT(*) FILTER (WHERE r.is_correct) AS float) / COUNT(*) ASC,
                                            COUNT(*) DESC,
                                            p.slug ASC
                            ) AS position_rank
                FROM        ({$rounds}) AS r
                    JOIN    pokemon AS p
                        ON  p.id = r.pokemon_id
                GROUP BY    r.game, p.id
                HAVING      COUNT(*) >= :min_answers
            ) AS s
            WHERE       s.position_rank <= :limit
            ORDER BY    s.game, s.position_rank
            SQL;

        /** @var list<array<string, mixed>> */
        return $this->getConnection()->fetchAllAssociative(
            $sql,
            [
                'min_answers' => $minAnswers,
                'limit' => $limit,
            ],
            [
                'min_answers' => ParameterType::INTEGER,
                'limit' => ParameterType::INTEGER,
            ],
        );
    }

    /**
     * Number of games started each day, zero included, from $since to $until.
     *
     * @return list<array<string, mixed>> ordered by day
     */
    public function getDailyGames(\DateTimeInterface $since, \DateTimeInterface $until): array
    {
        $games = self::ALL_GAMES;

        $sql = <<<SQL
            SELECT      TO_CHAR(d.day, 'YYYY-MM-DD') AS "date",
                        COUNT(g.game) FILTER (WHERE g.game = 'color_game') AS "color_game",
                        COUNT(g.game) FILTER (WHERE g.game = 'shiny_game') AS "shiny_game",
                        COUNT(g.game) FILTER (WHERE g.game = 'which_shiny_game') AS "which_shiny_game"
            FROM        GENERATE_SERIES(CAST(:since AS date), CAST(:until AS date), INTERVAL '1 day') AS d(day)
                LEFT JOIN ({$games}) AS g
                    ON  CAST(g.started_at AS date) = CAST(d.day AS date)
            GROUP BY    d.day
            ORDER BY    d.day
            SQL;

        /** @var list<array<string, mixed>> */
        return $this->getConnection()->fetchAllAssociative(
            $sql,
            [
                'since' => $since->format('Y-m-d'),
                'until' => $until->format('Y-m-d'),
            ],
        );
    }

    private function getConnection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
