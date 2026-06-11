<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameAvailabilityResponse::class)]
final class GameAvailabilityResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $game = new GameSlugResponse(slug: 'x');
        $response = new GameAvailabilityResponse(
            game: $game,
            isAvailable: true,
        );

        self::assertSame($game, $response->game);
        self::assertTrue($response->isAvailable);
    }

    #[Test]
    public function constructorAcceptsUnavailableGame(): void
    {
        $game = new GameSlugResponse(slug: 'blue');
        $response = new GameAvailabilityResponse(
            game: $game,
            isAvailable: false,
        );

        self::assertSame($game, $response->game);
        self::assertFalse($response->isAvailable);
    }
}
