<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundlesAvailabilitiesGroupResponse;
use App\DTO\Response\GamesAvailabilitiesGroupResponse;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponseFactory::class)]
#[CoversClass(GamesAvailabilitiesGroupResponse::class)]
#[CoversClass(GameBundlesAvailabilitiesGroupResponse::class)]
final class PokemonAvailabilitiesResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromAvailabilitiesBuildsResponseWithCorrectFieldMapping(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['games-key' => true]),
            new GamesShiniesAvailabilities(['shinies-key' => false]),
            new GameBundlesAvailabilities(['bundles-key' => true]),
            new GameBundlesShiniesAvailabilities(['bundlesshinies-key' => false]),
        );

        self::assertCount(1, $response->games->normal);
        self::assertInstanceOf(GameAvailabilityResponse::class, $response->games->normal[0]);
        self::assertSame('games-key', $response->games->normal[0]->game->slug);
        self::assertTrue($response->games->normal[0]->isAvailable);

        self::assertCount(1, $response->games->shiny);
        self::assertInstanceOf(GameAvailabilityResponse::class, $response->games->shiny[0]);
        self::assertSame('shinies-key', $response->games->shiny[0]->game->slug);
        self::assertFalse($response->games->shiny[0]->isAvailable);

        self::assertCount(1, $response->gameBundles->normal);
        self::assertInstanceOf(GameBundleAvailabilityResponse::class, $response->gameBundles->normal[0]);
        self::assertSame('bundles-key', $response->gameBundles->normal[0]->gameBundle->slug);
        self::assertTrue($response->gameBundles->normal[0]->isAvailable);

        self::assertCount(1, $response->gameBundles->shiny);
        self::assertInstanceOf(GameBundleAvailabilityResponse::class, $response->gameBundles->shiny[0]);
        self::assertSame('bundlesshinies-key', $response->gameBundles->shiny[0]->gameBundle->slug);
        self::assertFalse($response->gameBundles->shiny[0]->isAvailable);
    }

    #[Test]
    public function fromAvailabilitiesPreservesMapOrder(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['x' => true, 'y' => false, 'omegaruby' => true]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities(['goldsilvercrystal' => false, 'xy' => true]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertCount(3, $response->games->normal);
        self::assertSame('x', $response->games->normal[0]->game->slug);
        self::assertTrue($response->games->normal[0]->isAvailable);
        self::assertSame('y', $response->games->normal[1]->game->slug);
        self::assertFalse($response->games->normal[1]->isAvailable);
        self::assertSame('omegaruby', $response->games->normal[2]->game->slug);
        self::assertTrue($response->games->normal[2]->isAvailable);

        self::assertCount(2, $response->gameBundles->normal);
        self::assertSame('goldsilvercrystal', $response->gameBundles->normal[0]->gameBundle->slug);
        self::assertFalse($response->gameBundles->normal[0]->isAvailable);
        self::assertSame('xy', $response->gameBundles->normal[1]->gameBundle->slug);
        self::assertTrue($response->gameBundles->normal[1]->isAvailable);

        self::assertSame([], $response->games->shiny);
        self::assertSame([], $response->gameBundles->shiny);
    }

    #[Test]
    public function fromAvailabilitiesCastsNumericSlugsToString(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities(['123' => true]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities(['456' => false]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertSame('123', $response->games->normal[0]->game->slug);
        self::assertSame('456', $response->gameBundles->normal[0]->gameBundle->slug);

        self::assertSame([], $response->games->shiny);
        self::assertSame([], $response->gameBundles->shiny);
    }

    #[Test]
    public function fromAvailabilitiesHandlesEmptyAvailabilities(): void
    {
        $response = PokemonAvailabilitiesResponseFactory::fromAvailabilities(
            new GamesAvailabilities([]),
            new GamesShiniesAvailabilities([]),
            new GameBundlesAvailabilities([]),
            new GameBundlesShiniesAvailabilities([]),
        );

        self::assertSame([], $response->games->normal);
        self::assertSame([], $response->games->shiny);
        self::assertSame([], $response->gameBundles->normal);
        self::assertSame([], $response->gameBundles->shiny);
    }
}
