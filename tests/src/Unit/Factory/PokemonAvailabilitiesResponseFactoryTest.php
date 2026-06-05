<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
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

        self::assertSame(['games-key' => true], $response->gamesAvailabilities);
        self::assertSame(['shinies-key' => false], $response->gamesShiniesAvailabilities);
        self::assertSame(['bundles-key' => true], $response->gameBundlesAvailabilities);
        self::assertSame(['bundlesshinies-key' => false], $response->gameBundlesShiniesAvailabilities);
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
