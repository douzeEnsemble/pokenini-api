<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Service\Album\AlbumPokemonService;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use App\Tests\Common\Traits\PokemonListTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(AlbumPokemonService::class)]
class AlbumPokemonServiceFilteredTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;
    use PokemonListTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testListFilteredPrimaryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'primary_types' => [
                    'grass',
                ],
            ]),
        );

        $this->assertCount(6, $pokemons);

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
                'ivysaur',
                'venusaur',
                'venusaur-f',
                'venusaur-mega',
                'venusaur-gmax',
            ],
        );
    }

    public function testListFilteredSecondaryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'secondary_types' => [
                    'normal',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'rattata-alola',
                'raticate-alola',
                'raticate-alola-totem',
            ],
        );
    }

    public function testListFilteredPrimaryAndSecondaryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'primary_types' => [
                    'bug',
                ],
                'secondary_types' => [
                    'flying',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'butterfree',
                'butterfree-f',
                'butterfree-gmax',
            ],
        );
    }

    public function testListFilteredAnyType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'any_types' => [
                    'normal',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'rattata',
                'rattata-f',
                'rattata-alola',
                'raticate',
                'raticate-f',
                'raticate-alola',
                'raticate-alola-totem',
            ],
        );
    }

    public function testListFilteredCategoryType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'category_forms' => [
                    'starter',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
                'charmander',
            ],
        );
    }

    public function testListFilteredRegionalType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'regional_forms' => [
                    'alolan',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'rattata-alola',
                'raticate-alola',
                'raticate-alola-totem',
            ],
        );
    }

    public function testListFilteredSpecialType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'special_forms' => [
                    'gigantamax',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'venusaur-gmax',
                'butterfree-gmax',
            ],
        );
    }

    public function testListFilteredSpecialsType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'special_forms' => [
                    'gigantamax',
                    'mega',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'venusaur-mega',
                'venusaur-gmax',
                'butterfree-gmax',
            ],
        );
    }

    public function testListFilteredVariantType(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'variant_forms' => [
                    'gender',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'venusaur-f',
                'butterfree-f',
                'rattata-f',
                'raticate-f',
            ],
        );
    }

    public function testListFilteredCatchStates(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'catch_states' => [
                    'maybe',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'caterpie',
                'rattata-f',
                'raticate-f',
            ],
        );
    }

    public function testListFilteredOriginalGameBundle(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'original_game_bundles' => [
                    'redgreenblueyellow',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
                'ivysaur',
                'venusaur',
                'charmander',
                'charmeleon',
                'charizard',
                'caterpie',
                'metapod',
                'butterfree',
                'rattata',
                'raticate',
                'douze',
            ],
        );
    }

    public function testListFilteredGameBundleAvailabilities(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'game_bundle_availabilities' => [
                    'ultrasunultramoon',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'rattata-alola',
                'raticate-alola',
            ],
        );
    }

    public function testListFilteredGameBundleShinyAvailabilities(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'game_bundle_shiny_availabilities' => [
                    'ultrasunultramoon',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'rattata-f',
                'rattata-alola',
                'raticate',
                'raticate-f',
            ],
        );
    }

    public function testListFilteredFamilies(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'families' => [
                    'bulbasaur',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
                'ivysaur',
                'venusaur',
                'venusaur-f',
                'venusaur-mega',
                'venusaur-gmax',
            ],
        );
    }

    public function testListFilteredCollections(): void
    {
        /** @var AlbumPokemonService $service */
        $service = static::getContainer()->get(AlbumPokemonService::class);

        $pokemons = $service->get(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'collection_availabilities' => [
                    'pogoshadow',
                ],
            ]),
        );

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
            ],
        );
    }

    #[DataProvider('providerListFilteredNull')]
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
     * @return int[][]|string[][]
     */
    public static function providerListFilteredNull(): array
    {
        return [
            'primary_types' => [
                'primary_types',
                1,
            ],
            'secondary_types' => [
                'secondary_types',
                9,
            ],
            'category_forms' => [
                'category_forms',
                20,
            ],
            'regional_forms' => [
                'regional_forms',
                19,
            ],
            'special_forms' => [
                'special_forms',
                18,
            ],
            'variant_forms' => [
                'variant_forms',
                18,
            ],
            'catch_states' => [
                'catch_states',
                1,
            ],
            'original_game_bundles' => [
                'original_game_bundles',
                0,
            ],
            'game_bundle_availabilities' => [
                'game_bundle_availabilities',
                0,
            ],
            'game_bundle_shiny_availabilities' => [
                'game_bundle_shiny_availabilities',
                0,
            ],
            'collection_availabilities' => [
                'collection_availabilities',
                0,
            ],
        ];
    }
}
