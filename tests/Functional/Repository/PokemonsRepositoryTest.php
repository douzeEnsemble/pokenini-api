<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokemonsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountPokemonTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testRemoveAll(): void
    {
        $initCount = $this->getPokemonCount();
        $this->assertGreaterThan(0, $initCount);
        $this->assertEquals(0, $this->getPokemonDeletedCount());
        $this->assertGreaterThan(0, $this->getPokemonNotDeletedCount());

        /** @var PokemonsRepository $repo */
        $repo = static::getContainer()->get(PokemonsRepository::class);
        $repo->removeAll();

        $this->assertEquals($initCount, $this->getPokemonCount());
        $this->assertEquals($initCount, $this->getPokemonDeletedCount());
        $this->assertEquals(0, $this->getPokemonNotDeletedCount());
    }

    public function testGetAll(): void
    {
        /** @var PokemonsRepository $repo */
        $repo = static::getContainer()->get(PokemonsRepository::class);

        $pokemonsIterator = $repo->getQueryAll()->toIterable();

        /** @var Pokemon[] $pokemons */
        $pokemons = [...$pokemonsIterator];

        $this->assertCount($this->getPokemonCount(), $pokemons);

        $this->assertEquals('Bulbasaur', $pokemons[0]->name);
        $this->assertEquals(1, $pokemons[0]->nationalDexNumber);
        $this->assertEquals('bulbasaur', $pokemons[0]->family);

        $this->assertTrue($pokemons[3]->bankable);
        $this->assertNull($pokemons[3]->bankableish);
        $this->assertEquals('Diamond, Pearl, Platinium', $pokemons[3]->originalGameBundle->name);

        $this->assertNull($pokemons[5]->variantForm);
        $this->assertNull($pokemons[5]->regionalForm);
        $this->assertEquals('Gigantamax', $pokemons[5]->specialForm?->name);

        $this->assertEquals('charizard', $pokemons[8]->iconName);
        $this->assertEquals('butterfree', $pokemons[11]->iconName);

        $this->assertEquals('fire', $pokemons[8]->primaryType?->slug);
        $this->assertEquals('flying', $pokemons[8]->secondaryType?->slug);
    }

    public function testCountAll(): void
    {
        /** @var PokemonsRepository $repo */
        $repo = static::getContainer()->get(PokemonsRepository::class);

        $this->assertEquals($this->getPokemonCount(), $repo->countAll());
    }
}
