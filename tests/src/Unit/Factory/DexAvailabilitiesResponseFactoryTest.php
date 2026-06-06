<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\PokemonSlugResponse;
use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Factory\DexAvailabilitiesResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
final class DexAvailabilitiesResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromDexAvailabilitiesExtractsPokemonSlugsInOrder(): void
    {
        $pokemon1 = new Pokemon();
        $pokemon1->slug = 'bulbasaur';

        $pokemon2 = new Pokemon();
        $pokemon2->slug = 'ivysaur';

        $result = DexAvailabilitiesResponseFactory::fromDexAvailabilities([
            DexAvailability::create($pokemon1, new Dex()),
            DexAvailability::create($pokemon2, new Dex()),
        ]);

        self::assertCount(2, $result->pokemons);
        self::assertInstanceOf(PokemonSlugResponse::class, $result->pokemons[0]);
        self::assertInstanceOf(PokemonSlugResponse::class, $result->pokemons[1]);
        self::assertSame('bulbasaur', $result->pokemons[0]->slug);
        self::assertSame('ivysaur', $result->pokemons[1]->slug);
    }

    #[Test]
    public function fromDexAvailabilitiesHandlesEmptyArray(): void
    {
        $result = DexAvailabilitiesResponseFactory::fromDexAvailabilities([]);

        self::assertSame([], $result->pokemons);
    }
}
