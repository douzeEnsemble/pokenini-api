<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonEloResponse::class)]
final class PokemonEloResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new PokemonEloResponse(
            pokemonSlug: 'pikachu',
            elo: 1200,
        );

        self::assertSame('pikachu', $response->pokemonSlug);
        self::assertSame(1200, $response->elo);
    }

    #[Test]
    public function constructorAcceptsNegativeElo(): void
    {
        $response = new PokemonEloResponse(
            pokemonSlug: 'snorlax',
            elo: -500,
        );

        self::assertSame('snorlax', $response->pokemonSlug);
        self::assertSame(-500, $response->elo);
    }
}
