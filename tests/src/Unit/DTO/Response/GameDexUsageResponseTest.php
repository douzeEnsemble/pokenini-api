<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameDexUsageResponse;
use App\DTO\Response\ReportDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameDexUsageResponse::class)]
final class GameDexUsageResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $dex = new ReportDexResponse(slug: 'demo', name: 'Demo', frenchName: 'Démo');

        $response = new GameDexUsageResponse(
            dex: $dex,
            games: 4,
            players: 2,
            answered: 10,
            correct: 7,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame(4, $response->games);
        self::assertSame(2, $response->players);
        self::assertSame(10, $response->answered);
        self::assertSame(7, $response->correct);
    }
}
