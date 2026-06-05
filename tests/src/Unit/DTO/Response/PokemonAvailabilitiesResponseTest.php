<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

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
        $response = new PokemonAvailabilitiesResponse(
            gamesAvailabilities: ['x' => true, 'y' => false],
            gamesShiniesAvailabilities: ['x' => true],
            gameBundlesAvailabilities: ['goldsilvercrystal' => true],
            gameBundlesShiniesAvailabilities: ['goldsilvercrystal' => false],
        );

        self::assertSame(['x' => true, 'y' => false], $response->gamesAvailabilities);
        self::assertSame(['x' => true], $response->gamesShiniesAvailabilities);
        self::assertSame(['goldsilvercrystal' => true], $response->gameBundlesAvailabilities);
        self::assertSame(['goldsilvercrystal' => false], $response->gameBundlesShiniesAvailabilities);
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
