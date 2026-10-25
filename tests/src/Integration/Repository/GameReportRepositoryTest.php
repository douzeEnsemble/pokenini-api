<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\GameReportRepository;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameReportRepository::class)]
final class GameReportRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[Test]
    public function getGameCounts(): void
    {
        $counts = [
            'games_count' => 3,
            'played_count' => 2,
            'in_progress_count' => 2,
            'completed_count' => 1,
            'abandoned_count' => 0,
            'players_count' => 2,
            'round_count' => 6,
            'answered_count' => 3,
            'correct_count' => 2,
        ];

        $this->assertEquals(
            [
                ['game' => 'color_game', ...$counts],
                ['game' => 'shiny_game', ...$counts],
                ['game' => 'which_shiny_game', ...$counts],
            ],
            $this->getRepository()->getGameCounts(),
        );
    }

    #[Test]
    public function getGameCountsCountsAbandonedGames(): void
    {
        $this->getConnection()->executeStatement("UPDATE shiny_game SET status = 'abandoned' WHERE trainer_external_id = 'other_trainer'");

        $rows = array_column($this->getRepository()->getGameCounts(), null, 'game');

        $this->assertEquals(1, $rows['shiny_game']['in_progress_count']);
        $this->assertEquals(1, $rows['shiny_game']['abandoned_count']);
        $this->assertEquals(0, $rows['color_game']['abandoned_count']);
    }

    #[Test]
    public function getGameCountsWithoutGames(): void
    {
        $this->getConnection()->executeStatement('DELETE FROM color_game');
        $this->getConnection()->executeStatement('DELETE FROM shiny_game');

        $this->assertSame(
            ['which_shiny_game'],
            array_column($this->getRepository()->getGameCounts(), 'game'),
        );
    }

    #[Test]
    public function getDexUsage(): void
    {
        $this->assertEquals(
            [
                $this->getDexUsageRow('color_game', 'demo', 'Demo', 'Démo', 2, 2, 1, 1),
                $this->getDexUsageRow('color_game', 'homeshiny', "Home\nShiny", "Home\nChromatique", 1, 1, 2, 1),
                $this->getDexUsageRow('shiny_game', 'demo', 'Demo', 'Démo', 2, 2, 1, 1),
                $this->getDexUsageRow('shiny_game', 'redgreenblueyellow', 'Red / Green / Blue / Yellow', 'Rouge / Vert / Bleu / Jaune', 1, 1, 2, 1),
                $this->getDexUsageRow('which_shiny_game', 'demo', 'Demo', 'Démo', 2, 2, 1, 1),
                $this->getDexUsageRow('which_shiny_game', 'redgreenblueyellow', 'Red / Green / Blue / Yellow', 'Rouge / Vert / Bleu / Jaune', 1, 1, 2, 1),
            ],
            $this->getRepository()->getDexUsage(),
        );
    }

    #[Test]
    public function getHardestPokemons(): void
    {
        $rows = $this->getRepository()->getHardestPokemons(1, 2);

        $this->assertSame(
            [
                ['color_game', 'caterpie'],
                ['color_game', 'bulbasaur'],
                ['shiny_game', 'metapod'],
                ['shiny_game', 'bulbasaur'],
                ['which_shiny_game', 'metapod'],
                ['which_shiny_game', 'bulbasaur'],
            ],
            array_map(static fn (array $row): array => [$row['game'], $row['slug']], $rows),
        );

        $this->assertEquals(
            [
                'game' => 'color_game',
                'slug' => 'caterpie',
                'name' => 'Caterpie',
                'french_name' => 'Chenipan',
                'simplified_name' => 'Caterpie',
                'simplified_french_name' => 'Chenipan',
                'forms_label' => '',
                'forms_french_label' => '',
                'icon' => 'caterpie',
                'answered_count' => 1,
                'correct_count' => 0,
            ],
            $rows[0],
        );

        $this->assertEquals(2, $rows[1]['answered_count']);
        $this->assertEquals(2, $rows[1]['correct_count']);
    }

    #[Test]
    public function getHardestPokemonsWithMinAnswers(): void
    {
        $rows = $this->getRepository()->getHardestPokemons(2, 10);

        $this->assertSame(
            [['color_game', 'bulbasaur']],
            array_map(static fn (array $row): array => [$row['game'], $row['slug']], $rows),
        );
    }

    #[Test]
    public function getHardestPokemonsWithLimit(): void
    {
        $rows = $this->getRepository()->getHardestPokemons(1, 1);

        $this->assertSame(
            [
                ['color_game', 'caterpie'],
                ['shiny_game', 'metapod'],
                ['which_shiny_game', 'metapod'],
            ],
            array_map(static fn (array $row): array => [$row['game'], $row['slug']], $rows),
        );
    }

    #[Test]
    public function getDailyGames(): void
    {
        $rows = $this->getRepository()->getDailyGames(
            new \DateTimeImmutable('2026-10-01'),
            new \DateTimeImmutable('2026-10-12'),
        );

        $this->assertCount(12, $rows);

        $this->assertEquals(
            ['date' => '2026-10-01', 'color_game' => 1, 'shiny_game' => 1, 'which_shiny_game' => 1],
            $rows[0],
        );
        $this->assertEquals(
            ['date' => '2026-10-02', 'color_game' => 0, 'shiny_game' => 0, 'which_shiny_game' => 0],
            $rows[1],
        );
        $this->assertEquals(
            ['date' => '2026-10-10', 'color_game' => 1, 'shiny_game' => 1, 'which_shiny_game' => 1],
            $rows[9],
        );
        $this->assertEquals(
            ['date' => '2026-10-12', 'color_game' => 1, 'shiny_game' => 1, 'which_shiny_game' => 1],
            $rows[11],
        );
    }

    #[Test]
    public function getDailyGamesCountsEachGame(): void
    {
        $this->getConnection()->executeStatement("UPDATE shiny_game SET started_at = '2026-10-02 12:00:00' WHERE trainer_external_id = 'other_trainer'");

        $rows = $this->getRepository()->getDailyGames(
            new \DateTimeImmutable('2026-10-02'),
            new \DateTimeImmutable('2026-10-02'),
        );

        $this->assertEquals(
            [['date' => '2026-10-02', 'color_game' => 0, 'shiny_game' => 1, 'which_shiny_game' => 0]],
            $rows,
        );
    }

    /**
     * @return array<string, int|string>
     */
    private function getDexUsageRow(
        string $game,
        string $slug,
        string $name,
        string $frenchName,
        int $games,
        int $players,
        int $answered,
        int $correct,
    ): array {
        return [
            'game' => $game,
            'slug' => $slug,
            'name' => $name,
            'french_name' => $frenchName,
            'games_count' => $games,
            'players_count' => $players,
            'answered_count' => $answered,
            'correct_count' => $correct,
        ];
    }

    private function getRepository(): GameReportRepository
    {
        /** @var GameReportRepository */
        return self::getContainer()->get(GameReportRepository::class);
    }

    private function getConnection(): Connection
    {
        /** @var Connection */
        return self::getContainer()->get(Connection::class);
    }
}
