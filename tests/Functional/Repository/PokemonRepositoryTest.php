<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
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

        $this->assertTrue($pokemons[3]->bankable);
        $this->assertNull($pokemons[3]->bankableish);
        $this->assertEquals('Diamond, Pearl, Platinium', $pokemons[3]->originalGameBundle->name);

        $this->assertNull($pokemons[5]->variantForm);
        $this->assertNull($pokemons[5]->regionalForm);
        $this->assertEquals('Gigantamax', $pokemons[5]->specialForm?->name);

        $this->assertEquals('butterfree', $pokemons[8]->iconName);

        $this->assertEquals(
            'https://raw.githubusercontent.com/msikma/pokesprite/master/pokemon-gen8/regular/venusaur.png',
            $pokemons[2]->regularSpriteUrl
        );
        $this->assertEquals(
            'https://raw.githubusercontent.com/msikma/pokesprite/master/pokemon-gen8/shiny/venusaur.png',
            $pokemons[2]->shinySpriteUrl
        );
    }

    public function testCountAll(): void
    {
        /** @var PokemonRepository $repo */
        $repo = static::getContainer()->get(PokemonRepository::class);

        $this->assertEquals($this->getPokemonCount(), $repo->countAll());
    }
}
