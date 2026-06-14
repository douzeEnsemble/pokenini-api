<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundlesAvailabilitiesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\GamesAvailabilitiesGroupResponse;
use App\DTO\Response\GameSlugResponse;
use App\DTO\Response\PokemonAvailabilitiesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponse::class)]
#[CoversClass(GamesAvailabilitiesGroupResponse::class)]
#[CoversClass(GameBundlesAvailabilitiesGroupResponse::class)]
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

        $games = new GamesAvailabilitiesGroupResponse(
            normal: [$gameAvailability],
            shiny: [$gameShinyAvailability],
        );
        $gameBundles = new GameBundlesAvailabilitiesGroupResponse(
            normal: [$gameBundleAvailability],
            shiny: [$gameBundleShinyAvailability],
        );

        $response = new PokemonAvailabilitiesResponse(
            games: $games,
            gameBundles: $gameBundles,
        );

        self::assertSame($games, $response->games);
        self::assertSame([$gameAvailability], $response->games->normal);
        self::assertSame([$gameShinyAvailability], $response->games->shiny);
        self::assertSame($gameBundles, $response->gameBundles);
        self::assertSame([$gameBundleAvailability], $response->gameBundles->normal);
        self::assertSame([$gameBundleShinyAvailability], $response->gameBundles->shiny);
    }

    #[Test]
    public function constructorAcceptsEmptyArrays(): void
    {
        $games = new GamesAvailabilitiesGroupResponse(normal: [], shiny: []);
        $gameBundles = new GameBundlesAvailabilitiesGroupResponse(normal: [], shiny: []);

        $response = new PokemonAvailabilitiesResponse(
            games: $games,
            gameBundles: $gameBundles,
        );

        self::assertSame([], $response->games->normal);
        self::assertSame([], $response->games->shiny);
        self::assertSame([], $response->gameBundles->normal);
        self::assertSame([], $response->gameBundles->shiny);
    }
}
