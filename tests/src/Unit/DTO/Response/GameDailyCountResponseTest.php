<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameDailyCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameDailyCountResponse::class)]
final class GameDailyCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new GameDailyCountResponse(
            date: '2026-10-10',
            colorGame: 1,
            shinyGame: 2,
            whichShinyGame: 3,
        );

        self::assertSame('2026-10-10', $response->date);
        self::assertSame(1, $response->colorGame);
        self::assertSame(2, $response->shinyGame);
        self::assertSame(3, $response->whichShinyGame);
    }
}
