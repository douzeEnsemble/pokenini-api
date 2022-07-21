<?php

namespace App\Tests\Functionnal\Repository;

use App\Entity\Pokedex;
use App\Repository\PokedexRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokedexRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetQueryFromDexSlug(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexesIterator = $repo->getQueryFromDexSlug('redbluegreenyellow');

        /** @var string[][] $pokedexes */
        $pokedexes = iterator_to_array($pokedexesIterator);

        $this->assertCount(4, $pokedexes);

        $this->assertEquals('bulbasaur', $pokedexes[0]['pokemon_slug']);
        $this->assertEquals('No', $pokedexes[0]['catch_state_name']);
        $this->assertEquals('no', $pokedexes[0]['catch_state_slug']);

        $this->assertEquals('ivysaur', $pokedexes[1]['pokemon_slug']);
        $this->assertEquals('Maybe', $pokedexes[1]['catch_state_name']);
        $this->assertEquals('maybe', $pokedexes[1]['catch_state_slug']);

        $this->assertEquals('venusaur', $pokedexes[2]['pokemon_slug']);
        $this->assertEquals('Maybe not', $pokedexes[2]['catch_state_name']);
        $this->assertEquals('maybenot', $pokedexes[2]['catch_state_slug']);

        $this->assertEquals('douze', $pokedexes[3]['pokemon_slug']);
        $this->assertNull($pokedexes[3]['catch_state_name']);
        $this->assertNull($pokedexes[3]['catch_state_slug']);
    }
}
