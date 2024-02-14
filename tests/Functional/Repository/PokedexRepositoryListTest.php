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
    use PokedexRepositoryListDataTrait;

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

        $this->assertPokedexBulbasaur($pokedex);
        $this->assertPokedexIvysaur($pokedex);
        $this->assertPokedexVenusaur($pokedex);
        $this->assertPokedexCaterpie($pokedex);
        $this->assertPokedexDouze($pokedex);
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

    public function testGetListQueryAnyTypeFilter(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'anyTypes' => [
                    'normal',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(7, $pokedex);
        $this->assertEquals('rattata', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('rattata-alola', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('raticate', $pokedex[3]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokedex[4]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokedex[5]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokedex[6]['pokemon_slug']);
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

    public function testGetListQueryCatchStates(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'catchStates' => [
                    'maybe',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(3, $pokedex);
        $this->assertEquals('caterpie', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokedex[2]['pokemon_slug']);
    }

    public function testGetListQueryOriginalGameBundle(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'originalGameBundles' => [
                    'redgreenblueyellow',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(12, $pokedex);
        $this->assertEquals('bulbasaur', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('ivysaur', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('venusaur', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('charmander', $pokedex[3]['pokemon_slug']);
        $this->assertEquals('charmeleon', $pokedex[4]['pokemon_slug']);
        $this->assertEquals('charizard', $pokedex[5]['pokemon_slug']);
        $this->assertEquals('caterpie', $pokedex[6]['pokemon_slug']);
        $this->assertEquals('metapod', $pokedex[7]['pokemon_slug']);
        $this->assertEquals('butterfree', $pokedex[8]['pokemon_slug']);
        $this->assertEquals('rattata', $pokedex[9]['pokemon_slug']);
        $this->assertEquals('raticate', $pokedex[10]['pokemon_slug']);
        $this->assertEquals('douze', $pokedex[11]['pokemon_slug']);
    }

    public function testGetListQueryGameBundleAvailabilities(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'gameBundleAvailabilities' => [
                    'ultrasunultramoon',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(2, $pokedex);
        $this->assertEquals('rattata-alola', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokedex[1]['pokemon_slug']);
    }

    public function testGetListQueryGameBundleShinyAvailabilities(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $pokedexIterator = $repo->getListQuery(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'gameBundleShinyAvailabilities' => [
                    'ultrasunultramoon',
                ],
            ])
        );

        /** @var string[][] $pokedex */
        $pokedex = iterator_to_array($pokedexIterator);

        $this->assertCount(4, $pokedex);
        $this->assertEquals('rattata-f', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('rattata-alola', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('raticate', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokedex[3]['pokemon_slug']);
    }
}
