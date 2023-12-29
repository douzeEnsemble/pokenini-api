<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\PokedexRepository;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
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

    public function testgetListQuery(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow'
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(7, $pokedex);

        $this->assertEquals('Bulbasaur', $pokedex[0]['pokemon_name']);
        $this->assertEquals('Bulbizarre', $pokedex[0]['pokemon_french_name']);
        $this->assertEquals('bulbasaur', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('No', $pokedex[0]['catch_state_name']);
        $this->assertEquals('no', $pokedex[0]['catch_state_slug']);
        $this->assertEquals('Grass', $pokedex[0]['primary_type_name']);
        $this->assertEquals('Plante', $pokedex[0]['primary_type_french_name']);
        $this->assertEquals('grass', $pokedex[0]['primary_type_slug']);
        $this->assertEquals('Poison', $pokedex[0]['secondary_type_name']);
        $this->assertEquals('Poison', $pokedex[0]['secondary_type_french_name']);
        $this->assertEquals('poison', $pokedex[0]['secondary_type_slug']);

        $this->assertEquals('Ivysaur', $pokedex[1]['pokemon_name']);
        $this->assertEquals('Herbizarre', $pokedex[1]['pokemon_french_name']);
        $this->assertEquals('ivysaur', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('Maybe', $pokedex[1]['catch_state_name']);
        $this->assertEquals('maybe', $pokedex[1]['catch_state_slug']);
        $this->assertEquals('Grass', $pokedex[1]['primary_type_name']);
        $this->assertEquals('Plante', $pokedex[1]['primary_type_french_name']);
        $this->assertEquals('grass', $pokedex[1]['primary_type_slug']);
        $this->assertEquals('Poison', $pokedex[1]['secondary_type_name']);
        $this->assertEquals('Poison', $pokedex[1]['secondary_type_french_name']);
        $this->assertEquals('poison', $pokedex[1]['secondary_type_slug']);

        $this->assertEquals('Venusaur', $pokedex[2]['pokemon_name']);
        $this->assertEquals('Florizarre', $pokedex[2]['pokemon_french_name']);
        $this->assertEquals('venusaur', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('Maybe not', $pokedex[2]['catch_state_name']);
        $this->assertEquals('maybenot', $pokedex[2]['catch_state_slug']);
        $this->assertEquals('Grass', $pokedex[2]['primary_type_name']);
        $this->assertEquals('Plante', $pokedex[2]['primary_type_french_name']);
        $this->assertEquals('grass', $pokedex[2]['primary_type_slug']);
        $this->assertEquals('Poison', $pokedex[2]['secondary_type_name']);
        $this->assertEquals('Poison', $pokedex[2]['secondary_type_french_name']);
        $this->assertEquals('poison', $pokedex[2]['secondary_type_slug']);

        $this->assertEquals('Caterpie', $pokedex[3]['pokemon_name']);
        $this->assertEquals('Chenipan', $pokedex[3]['pokemon_french_name']);
        $this->assertEquals('caterpie', $pokedex[3]['pokemon_slug']);
        $this->assertEquals('Maybe not', $pokedex[3]['catch_state_name']);
        $this->assertEquals('maybenot', $pokedex[3]['catch_state_slug']);
        $this->assertEquals('Bug', $pokedex[3]['primary_type_name']);
        $this->assertEquals('Insecte', $pokedex[3]['primary_type_french_name']);
        $this->assertEquals('bug', $pokedex[3]['primary_type_slug']);
        $this->assertNull($pokedex[3]['secondary_type_name']);
        $this->assertNull($pokedex[3]['secondary_type_french_name']);
        $this->assertNull($pokedex[3]['secondary_type_slug']);

        $this->assertEquals('Douze', $pokedex[6]['pokemon_name']);
        $this->assertEquals('Douze', $pokedex[6]['pokemon_french_name']);
        $this->assertEquals('douze', $pokedex[6]['pokemon_slug']);
        $this->assertNull($pokedex[6]['catch_state_name']);
        $this->assertNull($pokedex[6]['catch_state_slug']);
        $this->assertNull($pokedex[6]['primary_type_name']);
        $this->assertNull($pokedex[6]['primary_type_french_name']);
        $this->assertNull($pokedex[6]['primary_type_slug']);
        $this->assertNull($pokedex[6]['secondary_type_name']);
        $this->assertNull($pokedex[6]['secondary_type_french_name']);
        $this->assertNull($pokedex[6]['secondary_type_slug']);
    }

    public function testGetCatchStatesCounts(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow'
        );

        $this->assertEquals(
            [
                [
                    'count' => 1,
                    'slug' => 'no',
                    'name' => 'No',
                    'french_name' => 'Non',
                ],
                [
                    'count' => 1,
                    'slug' => 'maybe',
                    'name' => 'Maybe',
                    'french_name' => 'Peut être',
                ],
                [
                    'count' => 2,
                    'slug' => 'maybenot',
                    'name' => 'Maybe not',
                    'french_name' => 'Peut être pas',
                ],
                [
                    'count' => 0,
                    'slug' => 'yes',
                    'name' => 'Yes',
                    'french_name' => 'Oui',
                ],
            ],
            $counts
        );
    }

    public function testUpdate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertEquals('Maybe', $pokedexBefore['name']);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $repo->upsert('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'ivysaur', 'yes');

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertEquals('Yes', $pokedexAfter['name']);
        $this->assertEquals('yes', $pokedexAfter['slug']);
    }

    public function testInsert(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEmpty($pokedexBefore);

        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $repo->upsert('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'douze', 'maybenot');

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEquals('Maybe not', $pokedexAfter['name']);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);
    }
}
