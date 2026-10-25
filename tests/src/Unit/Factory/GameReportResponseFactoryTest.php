<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\GameReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameReportResponseFactory::class)]
final class GameReportResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromServiceArraysReportsEveryGameInOrder(): void
    {
        $response = GameReportResponseFactory::fromServiceArrays(
            [
                $this->getCountsRow('which_shiny_game', 3),
                $this->getCountsRow('color_game', 5),
            ],
            [
                $this->getDexUsageRow('color_game', 'demo'),
                $this->getDexUsageRow('which_shiny_game', 'demo'),
                $this->getDexUsageRow('which_shiny_game', 'home'),
            ],
            [
                $this->getHardestPokemonRow('color_game', 'venusaur'),
                $this->getHardestPokemonRow('which_shiny_game', 'caterpie'),
                $this->getHardestPokemonRow('which_shiny_game', 'metapod'),
            ],
            [
                ['date' => '2026-10-10', 'color_game' => 1, 'shiny_game' => '2', 'which_shiny_game' => 3],
                ['date' => '2026-10-11', 'color_game' => 0, 'shiny_game' => 0, 'which_shiny_game' => 0],
            ],
        );

        self::assertSame(['color_game', 'shiny_game', 'which_shiny_game'], array_map(static fn ($stats) => $stats->game, $response->games));

        [$color, $shiny, $whichShiny] = $response->games;

        self::assertSame(5, $color->games->total);
        self::assertSame([0], array_keys($color->dexUsage));
        self::assertSame('demo', $color->dexUsage[0]->dex->slug);
        self::assertSame([0], array_keys($color->hardestPokemons));
        self::assertSame('venusaur', $color->hardestPokemons[0]->pokemon->slug);

        self::assertSame(0, $shiny->games->total);
        self::assertSame(0, $shiny->games->played);
        self::assertSame(0, $shiny->games->players);
        self::assertSame(0, $shiny->rounds->planned);
        self::assertSame(0, $shiny->rounds->answered);
        self::assertSame(0, $shiny->rounds->correct);
        self::assertSame([], $shiny->dexUsage);
        self::assertSame([], $shiny->hardestPokemons);

        self::assertSame(3, $whichShiny->games->total);
        self::assertSame([0, 1], array_keys($whichShiny->dexUsage));
        self::assertSame('demo', $whichShiny->dexUsage[0]->dex->slug);
        self::assertSame('home', $whichShiny->dexUsage[1]->dex->slug);
        self::assertSame([0, 1], array_keys($whichShiny->hardestPokemons));
        self::assertSame('caterpie', $whichShiny->hardestPokemons[0]->pokemon->slug);
        self::assertSame('metapod', $whichShiny->hardestPokemons[1]->pokemon->slug);

        self::assertCount(2, $response->dailyGames);
        self::assertSame('2026-10-10', $response->dailyGames[0]->date);
        self::assertSame(1, $response->dailyGames[0]->colorGame);
        self::assertSame(2, $response->dailyGames[0]->shinyGame);
        self::assertSame(3, $response->dailyGames[0]->whichShinyGame);
        self::assertSame('2026-10-11', $response->dailyGames[1]->date);
    }

    #[Test]
    public function fromServiceArraysWithoutAnyData(): void
    {
        $response = GameReportResponseFactory::fromServiceArrays([], [], [], []);

        self::assertCount(3, $response->games);
        self::assertSame([], $response->dailyGames);
    }

    #[Test]
    public function fromCountsRowCastsToInt(): void
    {
        $response = GameReportResponseFactory::fromCountsRow([
            'game' => 'color_game',
            'games_count' => '6',
            'played_count' => '5',
            'in_progress_count' => '2',
            'completed_count' => '3',
            'abandoned_count' => '1',
            'players_count' => '4',
        ]);

        self::assertSame(6, $response->total);
        self::assertSame(5, $response->played);
        self::assertSame(2, $response->inProgress);
        self::assertSame(3, $response->completed);
        self::assertSame(1, $response->abandoned);
        self::assertSame(4, $response->players);
    }

    #[Test]
    public function fromRoundCountsRowCastsToInt(): void
    {
        $response = GameReportResponseFactory::fromRoundCountsRow([
            'round_count' => '30',
            'answered_count' => '20',
            'correct_count' => '12',
        ]);

        self::assertSame(30, $response->planned);
        self::assertSame(20, $response->answered);
        self::assertSame(12, $response->correct);
    }

    #[Test]
    public function fromDexUsageRowTransformsRow(): void
    {
        $response = GameReportResponseFactory::fromDexUsageRow([
            'game' => 'color_game',
            'slug' => 'demo',
            'name' => 'Demo',
            'french_name' => 'Démo',
            'games_count' => '4',
            'players_count' => '2',
            'answered_count' => '10',
            'correct_count' => '7',
        ]);

        self::assertSame('demo', $response->dex->slug);
        self::assertSame('Demo', $response->dex->name);
        self::assertSame('Démo', $response->dex->frenchName);
        self::assertSame(4, $response->games);
        self::assertSame(2, $response->players);
        self::assertSame(10, $response->answered);
        self::assertSame(7, $response->correct);
    }

    #[Test]
    public function fromHardestPokemonRowTransformsRow(): void
    {
        $response = GameReportResponseFactory::fromHardestPokemonRow([
            'game' => 'shiny_game',
            'slug' => 'venusaur-f',
            'name' => 'Venusaur',
            'french_name' => 'Florizarre',
            'simplified_name' => 'Venusaur',
            'simplified_french_name' => 'Florizarre',
            'forms_label' => 'Female',
            'forms_french_label' => 'Femelle',
            'icon' => 'venusaur-f',
            'answered_count' => '5',
            'correct_count' => '1',
        ]);

        self::assertSame('venusaur-f', $response->pokemon->slug);
        self::assertSame('Venusaur', $response->pokemon->name);
        self::assertSame('Florizarre', $response->pokemon->frenchName);
        self::assertSame('Venusaur', $response->pokemon->simplifiedName);
        self::assertSame('Florizarre', $response->pokemon->simplifiedFrenchName);
        self::assertSame('Female', $response->pokemon->formsLabel);
        self::assertSame('Femelle', $response->pokemon->formsFrenchLabel);
        self::assertSame('venusaur-f', $response->pokemon->icon);
        self::assertSame(5, $response->answered);
        self::assertSame(1, $response->correct);
    }

    #[Test]
    public function fromDailyGamesRowCastsTypes(): void
    {
        $response = GameReportResponseFactory::fromDailyGamesRow([
            'date' => '2026-10-10',
            'color_game' => '1',
            'shiny_game' => '2',
            'which_shiny_game' => '3',
        ]);

        self::assertSame('2026-10-10', $response->date);
        self::assertSame(1, $response->colorGame);
        self::assertSame(2, $response->shinyGame);
        self::assertSame(3, $response->whichShinyGame);
    }

    /**
     * @return array<string, int|string>
     */
    private function getCountsRow(string $game, int $total): array
    {
        return [
            'game' => $game,
            'games_count' => $total,
            'played_count' => $total,
            'in_progress_count' => 1,
            'completed_count' => $total - 1,
            'abandoned_count' => 0,
            'players_count' => 1,
            'round_count' => 10,
            'answered_count' => 8,
            'correct_count' => 5,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function getDexUsageRow(string $game, string $slug): array
    {
        return [
            'game' => $game,
            'slug' => $slug,
            'name' => $slug,
            'french_name' => $slug,
            'games_count' => 1,
            'players_count' => 1,
            'answered_count' => 2,
            'correct_count' => 1,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function getHardestPokemonRow(string $game, string $slug): array
    {
        return [
            'game' => $game,
            'slug' => $slug,
            'name' => $slug,
            'french_name' => $slug,
            'simplified_name' => $slug,
            'simplified_french_name' => $slug,
            'forms_label' => '',
            'forms_french_label' => '',
            'icon' => $slug,
            'answered_count' => 3,
            'correct_count' => 0,
        ];
    }
}
