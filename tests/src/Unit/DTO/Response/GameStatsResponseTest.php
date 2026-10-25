<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameCountsResponse;
use App\DTO\Response\GameDexUsageResponse;
use App\DTO\Response\GameRoundCountsResponse;
use App\DTO\Response\GameStatsResponse;
use App\DTO\Response\ReportDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameStatsResponse::class)]
final class GameStatsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $games = new GameCountsResponse(total: 1, played: 1, inProgress: 0, completed: 1, abandoned: 0, players: 1);
        $rounds = new GameRoundCountsResponse(planned: 2, answered: 2, correct: 1);
        $dexUsage = [
            new GameDexUsageResponse(
                dex: new ReportDexResponse(slug: 'demo', name: 'Demo', frenchName: 'Démo'),
                games: 1,
                players: 1,
                answered: 2,
                correct: 1,
            ),
        ];

        $response = new GameStatsResponse(
            game: 'shiny_game',
            games: $games,
            rounds: $rounds,
            dexUsage: $dexUsage,
            hardestPokemons: [],
        );

        self::assertSame('shiny_game', $response->game);
        self::assertSame($games, $response->games);
        self::assertSame($rounds, $response->rounds);
        self::assertSame($dexUsage, $response->dexUsage);
        self::assertSame([], $response->hardestPokemons);
    }
}
