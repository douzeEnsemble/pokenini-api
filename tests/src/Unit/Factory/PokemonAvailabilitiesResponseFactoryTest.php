<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\Factory\PokemonAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilitiesResponseFactory::class)]
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

        self::assertCount(1, $response->gamesAvailabilities);
        self::assertInstanceOf(GameAvailabilityResponse::class, $response->gamesAvailabilities[0]);
        self::assertSame('games-key', $response->gamesAvailabilities[0]->game->slug);
        self::assertTrue($response->gamesAvailabilities[0]->isAvailable);

        self::assertCount(1, $response->gamesShiniesAvailabilities);
        self::assertInstanceOf(GameAvailabilityResponse::class, $response->gamesShiniesAvailabilities[0]);
        self::assertSame('shinies-key', $response->gamesShiniesAvailabilities[0]->game->slug);
        self::assertFalse($response->gamesShiniesAvailabilities[0]->isAvailable);

        self::assertCount(1, $response->gameBundlesAvailabilities);
        self::assertInstanceOf(GameBundleAvailabilityResponse::class, $response->gameBundlesAvailabilities[0]);
        self::assertSame('bundles-key', $response->gameBundlesAvailabilities[0]->gameBundle->slug);
        self::assertTrue($response->gameBundlesAvailabilities[0]->isAvailable);

        self::assertCount(1, $response->gameBundlesShiniesAvailabilities);
        self::assertInstanceOf(GameBundleAvailabilityResponse::class, $response->gameBundlesShiniesAvailabilities[0]);
        self::assertSame('bundlesshinies-key', $response->gameBundlesShiniesAvailabilities[0]->gameBundle->slug);
        self::assertFalse($response->gameBundlesShiniesAvailabilities[0]->isAvailable);
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

        self::assertCount(3, $response->gamesAvailabilities);
        self::assertSame('x', $response->gamesAvailabilities[0]->game->slug);
        self::assertTrue($response->gamesAvailabilities[0]->isAvailable);
        self::assertSame('y', $response->gamesAvailabilities[1]->game->slug);
        self::assertFalse($response->gamesAvailabilities[1]->isAvailable);
        self::assertSame('omegaruby', $response->gamesAvailabilities[2]->game->slug);
        self::assertTrue($response->gamesAvailabilities[2]->isAvailable);

        self::assertCount(2, $response->gameBundlesAvailabilities);
        self::assertSame('goldsilvercrystal', $response->gameBundlesAvailabilities[0]->gameBundle->slug);
        self::assertFalse($response->gameBundlesAvailabilities[0]->isAvailable);
        self::assertSame('xy', $response->gameBundlesAvailabilities[1]->gameBundle->slug);
        self::assertTrue($response->gameBundlesAvailabilities[1]->isAvailable);

        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
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

        self::assertSame('123', $response->gamesAvailabilities[0]->game->slug);
        self::assertSame('456', $response->gameBundlesAvailabilities[0]->gameBundle->slug);

        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
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

        self::assertSame([], $response->gamesAvailabilities);
        self::assertSame([], $response->gamesShiniesAvailabilities);
        self::assertSame([], $response->gameBundlesAvailabilities);
        self::assertSame([], $response->gameBundlesShiniesAvailabilities);
    }
}
