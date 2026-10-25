<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameCountsResponse;
use App\DTO\Response\GameDailyCountResponse;
use App\DTO\Response\GameReportResponse;
use App\DTO\Response\GameRoundCountsResponse;
use App\DTO\Response\GameStatsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameReportResponse::class)]
final class GameReportResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $games = [
            new GameStatsResponse(
                game: 'color_game',
                games: new GameCountsResponse(total: 0, played: 0, inProgress: 0, completed: 0, abandoned: 0, players: 0),
                rounds: new GameRoundCountsResponse(planned: 0, answered: 0, correct: 0),
                dexUsage: [],
                hardestPokemons: [],
            ),
        ];
        $dailyGames = [
            new GameDailyCountResponse(date: '2026-10-10', colorGame: 1, shinyGame: 0, whichShinyGame: 0),
        ];

        $response = new GameReportResponse(
            games: $games,
            dailyGames: $dailyGames,
        );

        self::assertSame($games, $response->games);
        self::assertSame($dailyGames, $response->dailyGames);
    }
}
