<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonSlugResponse;
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
        $pokemon = new PokemonSlugResponse(slug: 'pikachu');
        $response = new PokemonEloResponse(
            pokemon: $pokemon,
            elo: 1200,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame('pikachu', $response->pokemon->slug);
        self::assertSame(1200, $response->elo);
    }

    #[Test]
    public function constructorAcceptsNegativeElo(): void
    {
        $response = new PokemonEloResponse(
            pokemon: new PokemonSlugResponse(slug: 'snorlax'),
            elo: -500,
        );

        self::assertSame('snorlax', $response->pokemon->slug);
        self::assertSame(-500, $response->elo);
    }
}
