<?php

namespace App\Tests\functionnal\Repository;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Tests\resources\functionnal\CountPokemonTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokemonRepositoryTest extends KernelTestCase
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

        /** @var PokemonRepository $repo */
        $repo = static::getContainer()->get(PokemonRepository::class);
        $repo->removeAll();

        $this->assertEquals($initCount, $this->getPokemonCount());
        $this->assertEquals($initCount, $this->getPokemonDeletedCount());
        $this->assertEquals(0, $this->getPokemonNotDeletedCount());
    }

    public function testGetAll(): void
    {
        /** @var PokemonRepository $repo */
        $repo = static::getContainer()->get(PokemonRepository::class);

        $pokemonsIterator = $repo->getQueryAll()->toIterable();

        /** @var Pokemon[] $pokemons */
        $pokemons = iterator_to_array($pokemonsIterator);

        $this->assertCount($this->getPokemonCount(), $pokemons);

        $this->assertEquals('Bulbasaur', $pokemons[0]->name);
        $this->assertEquals(1, $pokemons[0]->nationalDexNumber);
        $this->assertNull($pokemons[0]->family);

        $this->assertTrue($pokemons[1]->bankable);
        $this->assertNull($pokemons[1]->bankableish);
        $this->assertEquals('Red, Green, Blue, Yellow', $pokemons[1]->originalGameBundle->name);

        $this->assertNull($pokemons[2]->variantForm);
        $this->assertNull($pokemons[2]->regionalForm);
        $this->assertEquals('Gigantamax', $pokemons[2]->specialForm?->name);

        $this->assertEquals('ivysaur', $pokemons[3]->iconName);
    }

    public function testCountAll(): void
    {
        /** @var PokemonRepository $repo */
        $repo = static::getContainer()->get(PokemonRepository::class);

        $this->assertEquals($this->getPokemonCount(), $repo->countAll());
    }
}
