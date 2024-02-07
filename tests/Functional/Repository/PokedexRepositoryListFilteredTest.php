<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokedexRepositoryListFilteredTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;

    public function setUp(): void
    {
        self::bootKernel();
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
