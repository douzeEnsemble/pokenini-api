<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokedexRepositoryListTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetListQuery(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
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
        $this->assertEquals('poison', $pokedex[0]['secondary_type_slug']);
        $this->assertEquals('redgreenblueyellow', $pokedex[0]['original_game_bundle_slug']);

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
        $this->assertEquals('redgreenblueyellow', $pokedex[0]['original_game_bundle_slug']);

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
        $this->assertEquals('redgreenblueyellow', $pokedex[0]['original_game_bundle_slug']);

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
        $this->assertEquals('redgreenblueyellow', $pokedex[0]['original_game_bundle_slug']);

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
        $this->assertEquals('redgreenblueyellow', $pokedex[0]['original_game_bundle_slug']);
    }

    public function testGetListQueryPrimaryTypeFilter(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'primaryTypes' => [
                    'grass',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(6, $pokedex);
        $this->assertEquals('bulbasaur', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('ivysaur', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('venusaur', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('venusaur-f', $pokedex[3]['pokemon_slug']);
        $this->assertEquals('venusaur-mega', $pokedex[4]['pokemon_slug']);
        $this->assertEquals('venusaur-gmax', $pokedex[5]['pokemon_slug']);
    }

    public function testGetListQuerySecondaryTypeFilter(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'secondaryTypes' => [
                    'normal',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(3, $pokedex);
        $this->assertEquals('rattata-alola', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokedex[2]['pokemon_slug']);
    }

    public function testGetListQueryPrimaryAndSecondaryTypeFilter(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'primaryTypes' => [
                    'bug',
                ],
                'secondaryTypes' => [
                    'flying',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(3, $pokedex);
        $this->assertEquals('butterfree', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('butterfree-f', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokedex[2]['pokemon_slug']);
    }

    public function testGetListQueryCategoryForm(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'categoryForms' => [
                    'starter',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(2, $pokedex);
        $this->assertEquals('bulbasaur', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('charmander', $pokedex[1]['pokemon_slug']);
    }

    public function testGetListQueryRegionalForm(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'regionalForms' => [
                    'alolan',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(3, $pokedex);
        $this->assertEquals('rattata-alola', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokedex[2]['pokemon_slug']);
    }

    public function testGetListQuerySpecialForm(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'specialForms' => [
                    'gigantamax',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(2, $pokedex);
        $this->assertEquals('venusaur-gmax', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokedex[1]['pokemon_slug']);
    }

    public function testGetListQuerySpecialsForm(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'specialForms' => [
                    'gigantamax',
                    'mega',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(3, $pokedex);
        $this->assertEquals('venusaur-mega', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('venusaur-gmax', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokedex[2]['pokemon_slug']);
    }

    public function testGetListQueryVariantForm(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'variantForms' => [
                    'gender',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(4, $pokedex);
        $this->assertEquals('venusaur-f', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('butterfree-f', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokedex[3]['pokemon_slug']);
    }
}
