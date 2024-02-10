<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Service\Album\AlbumPokemonService;
use App\Tests\Common\Data\AlbumData;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumPokemonServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testListUser12RedGreenBlueYellow(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                'no',
                'maybe',
                'maybenot',
                'maybenot',
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListUser12GoldSilverCrystal(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'goldsilvercrystal',
            AlbumFilters::createFromArray([]),
        );

        $this->assertEquals(
            AlbumData::getExpectedGoldSilverCrystalContent(
                'yes',
                'no',
                'no',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListUser13(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                'yes',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListUserUnknown(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '46546542313186',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowContent(
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );
    }

    public function testListFilteredPrimaryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'primaryTypes' => [
                    'grass',
                ],
            ]),
        );

        $this->assertCount(6, $pokemons);
        $this->assertEquals('bulbasaur', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('ivysaur', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('venusaur', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('venusaur-f', $pokemons[3]['pokemon_slug']);
        $this->assertEquals('venusaur-mega', $pokemons[4]['pokemon_slug']);
        $this->assertEquals('venusaur-gmax', $pokemons[5]['pokemon_slug']);
    }

    public function testListFilteredSecondaryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'secondaryTypes' => [
                    'normal',
                ],
            ]),
        );

        $this->assertCount(3, $pokemons);
        $this->assertEquals('rattata-alola', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokemons[2]['pokemon_slug']);
    }

    public function testListFilteredPrimaryAndSecondaryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'primaryTypes' => [
                    'bug',
                ],
                'secondaryTypes' => [
                    'flying',
                ],
            ]),
        );

        $this->assertCount(3, $pokemons);
        $this->assertEquals('butterfree', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('butterfree-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokemons[2]['pokemon_slug']);
    }

    public function testListFilteredAnyType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'anyTypes' => [
                    'normal',
                ],
            ]),
        );

        $this->assertCount(7, $pokemons);
        $this->assertEquals('rattata', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('rattata-alola', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('raticate', $pokemons[3]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokemons[4]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokemons[5]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokemons[6]['pokemon_slug']);
    }

    public function testListFilteredCategoryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'categoryForms' => [
                    'starter',
                ],
            ]),
        );

        $this->assertCount(2, $pokemons);
        $this->assertEquals('bulbasaur', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('charmander', $pokemons[1]['pokemon_slug']);
    }

    public function testListFilteredRegionalType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'regionalForms' => [
                    'alolan',
                ],
            ]),
        );

        $this->assertCount(3, $pokemons);
        $this->assertEquals('rattata-alola', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokemons[2]['pokemon_slug']);
    }

    public function testListFilteredSpecialType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'specialForms' => [
                    'gigantamax',
                ],
            ]),
        );

        $this->assertCount(2, $pokemons);
        $this->assertEquals('venusaur-gmax', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokemons[1]['pokemon_slug']);
    }

    public function testListFilteredSpecialsType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'specialForms' => [
                    'gigantamax',
                    'mega',
                ],
            ]),
        );

        $this->assertCount(3, $pokemons);
        $this->assertEquals('venusaur-mega', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('venusaur-gmax', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokemons[2]['pokemon_slug']);
    }

    public function testListFilteredVariantType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'variantForms' => [
                    'gender',
                ],
            ]),
        );

        $this->assertCount(4, $pokemons);
        $this->assertEquals('venusaur-f', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('butterfree-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokemons[3]['pokemon_slug']);
    }

    /**
     * @dataProvider providerListFilteredNull
     */
    public function testListFilteredNull(string $filter, int $expectedCount): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                $filter => [
                    'null',
                ],
            ]),
        );

        $this->assertCount($expectedCount, $pokemons);
    }

    /**
     * @return string[][]|int[][]
     */
    public function providerListFilteredNull(): array
    {
        return [
            'primary_types' => [
                'primaryTypes',
                1,
            ],
            'secondary_types' => [
                'secondaryTypes',
                9,
            ],
            'category_forms' => [
                'categoryForms',
                20,
            ],
            'regional_forms' => [
                'regionalForms',
                19,
            ],
            'special_forms' => [
                'specialForms',
                18,
            ],
            'variant_forms' => [
                'variantForms',
                18,
            ],
            'catch_states' => [
                'catchStates',
                1,
            ],
        ];
    }
}
