<?php

namespace App\Tests\Functionnal\Repository;

use App\Entity\Pokedex;
use App\Repository\PokedexRepository;
use App\Tests\Resources\functionnal\GetterTrait\GetPokedexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokedexRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetQueryFromDexSlug(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexesIterator = $repo->getQueryFromDexSlug('redgreenblueyellow');

        /** @var string[][] $pokedexes */
        $pokedexes = iterator_to_array($pokedexesIterator);

        $this->assertCount(4, $pokedexes);

        $this->assertEquals('Bulbasaur', $pokedexes[0]['pokemon_name']);
        $this->assertEquals('Bulbizarre', $pokedexes[0]['pokemon_french_name']);
        $this->assertEquals('bulbasaur', $pokedexes[0]['pokemon_slug']);
        $this->assertEquals('No', $pokedexes[0]['catch_state_name']);
        $this->assertEquals('no', $pokedexes[0]['catch_state_slug']);

        $this->assertEquals('Ivysaur', $pokedexes[1]['pokemon_name']);
        $this->assertEquals('Herbizarre', $pokedexes[1]['pokemon_french_name']);
        $this->assertEquals('ivysaur', $pokedexes[1]['pokemon_slug']);
        $this->assertEquals('Maybe', $pokedexes[1]['catch_state_name']);
        $this->assertEquals('maybe', $pokedexes[1]['catch_state_slug']);

        $this->assertEquals('Venusaur', $pokedexes[2]['pokemon_name']);
        $this->assertEquals('Florizarre', $pokedexes[2]['pokemon_french_name']);
        $this->assertEquals('venusaur', $pokedexes[2]['pokemon_slug']);
        $this->assertEquals('Maybe not', $pokedexes[2]['catch_state_name']);
        $this->assertEquals('maybenot', $pokedexes[2]['catch_state_slug']);

        $this->assertEquals('Douze', $pokedexes[3]['pokemon_name']);
        $this->assertEquals('Douze', $pokedexes[3]['pokemon_french_name']);
        $this->assertEquals('douze', $pokedexes[3]['pokemon_slug']);
        $this->assertNull($pokedexes[3]['catch_state_name']);
        $this->assertNull($pokedexes[3]['catch_state_slug']);
    }

    public function testUpdateFromSlugs(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertEquals('Maybe', $pokedexBefore['name']);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $repo->upsertFromSlugs('redgreenblueyellow', 'ivysaur', 'yes');

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertEquals('Yes', $pokedexAfter['name']);
        $this->assertEquals('yes', $pokedexAfter['slug']);
    }

    public function testInsertFromSlugs(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEmpty($pokedexBefore);

        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $repo->upsertFromSlugs('redgreenblueyellow', 'douze', 'maybenot');

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEquals('Maybe not', $pokedexAfter['name']);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);
    }
}
