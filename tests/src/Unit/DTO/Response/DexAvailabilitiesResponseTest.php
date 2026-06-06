<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexAvailabilitiesResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesResponse::class)]
final class DexAvailabilitiesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $pokemon1 = new PokemonSlugResponse(slug: 'bulbasaur');
        $pokemon2 = new PokemonSlugResponse(slug: 'ivysaur');

        $response = new DexAvailabilitiesResponse(
            pokemons: [$pokemon1, $pokemon2],
        );

        self::assertCount(2, $response->pokemons);
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemons[0]);
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemons[1]);
        self::assertSame('bulbasaur', $response->pokemons[0]->slug);
        self::assertSame('ivysaur', $response->pokemons[1]->slug);
    }

    #[Test]
    public function constructorAcceptsEmptyArray(): void
    {
        $response = new DexAvailabilitiesResponse(
            pokemons: [],
        );

        self::assertSame([], $response->pokemons);
    }
}
