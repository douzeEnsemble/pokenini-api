<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\GameSlugResponse;
use App\DTO\Response\PokemonAvailabilitiesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponse::class)]
final class PokemonAvailabilitiesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $gameAvailability = new GameAvailabilityResponse(
            game: new GameSlugResponse(slug: 'x'),
            isAvailable: true,
        );
        $gameShinyAvailability = new GameAvailabilityResponse(
            game: new GameSlugResponse(slug: 'y'),
            isAvailable: false,
        );
        $gameBundleAvailability = new GameBundleAvailabilityResponse(
            gameBundle: new GameBundleSlugResponse(slug: 'xy'),
            isAvailable: true,
        );
        $gameBundleShinyAvailability = new GameBundleAvailabilityResponse(
            gameBundle: new GameBundleSlugResponse(slug: 'goldsilvercrystal'),
            isAvailable: false,
        );

        $response = new PokemonAvailabilitiesResponse(
            gamesAvailabilities: [$gameAvailability],
            gamesShiniesAvailabilities: [$gameShinyAvailability],
            gameBundlesAvailabilities: [$gameBundleAvailability],
            gameBundlesShiniesAvailabilities: [$gameBundleShinyAvailability],
        );

        self::assertSame([$gameAvailability], $response->gamesAvailabilities);
        self::assertSame([$gameShinyAvailability], $response->gamesShiniesAvailabilities);
        self::assertSame([$gameBundleAvailability], $response->gameBundlesAvailabilities);
        self::assertSame([$gameBundleShinyAvailability], $response->gameBundlesShiniesAvailabilities);
    }

    #[Test]
    public function constructorAcceptsEmptyArrays(): void
    {
        $response = new PokemonAvailabilitiesResponse(
            gamesAvailabilities: [],
            gamesShiniesAvailabilities: [],
            gameBundlesAvailabilities: [],
            gameBundlesShiniesAvailabilities: [],
        );

        self::assertSame([], $response->gamesAvailabilities);
        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
    }
}
