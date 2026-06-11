<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundleSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundleAvailabilityResponse::class)]
final class GameBundleAvailabilityResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $gameBundle = new GameBundleSlugResponse(slug: 'xy');
        $response = new GameBundleAvailabilityResponse(
            gameBundle: $gameBundle,
            isAvailable: true,
        );

        self::assertSame($gameBundle, $response->gameBundle);
        self::assertTrue($response->isAvailable);
    }

    #[Test]
    public function constructorAcceptsUnavailableGameBundle(): void
    {
        $gameBundle = new GameBundleSlugResponse(slug: 'goldsilvercrystal');
        $response = new GameBundleAvailabilityResponse(
            gameBundle: $gameBundle,
            isAvailable: false,
        );

        self::assertSame($gameBundle, $response->gameBundle);
        self::assertFalse($response->isAvailable);
    }
}
