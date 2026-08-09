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
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(AlbumPokemonService::class)]
final class AlbumPokemonServiceFilteredTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameBundleAvailabilityTrait;
    use PokemonListTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function listFilteredPrimaryType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredSecondaryType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredPrimaryAndSecondaryType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredAnyType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredCategoryType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredRegionalType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredSpecialType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredSpecialsType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredVariantType(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredCatchStates(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredOriginalGameBundle(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredGameBundleAvailabilities(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredGameBundleShinyAvailabilities(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredFamilies(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    public function listFilteredCollections(): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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

    #[Test]
    #[DataProvider('providerListFilteredNull')]
    public function listFilteredNull(string $filter, int $expectedCount): void
    {
        $service = self::getContainer()->get(AlbumPokemonService::class);

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
     * @return array<string, array{
     *  filter: string,
     *  expectedCount: int,
     * }>
     */
    public static function providerListFilteredNull(): array
    {
        return [
            'primary_types' => [
                'filter' => 'primary_types',
                'expectedCount' => 1,
            ],
            'secondary_types' => [
                'filter' => 'secondary_types',
                'expectedCount' => 9,
            ],
            'category_forms' => [
                'filter' => 'category_forms',
                'expectedCount' => 20,
            ],
            'regional_forms' => [
                'filter' => 'regional_forms',
                'expectedCount' => 19,
            ],
            'special_forms' => [
                'filter' => 'special_forms',
                'expectedCount' => 18,
            ],
            'variant_forms' => [
                'filter' => 'variant_forms',
                'expectedCount' => 18,
            ],
            'catch_states' => [
                'filter' => 'catch_states',
                'expectedCount' => 1,
            ],
            'original_game_bundles' => [
                'filter' => 'original_game_bundles',
                'expectedCount' => 0,
            ],
            'game_bundle_availabilities' => [
                'filter' => 'game_bundle_availabilities',
                'expectedCount' => 0,
            ],
            'game_bundle_shiny_availabilities' => [
                'filter' => 'game_bundle_shiny_availabilities',
                'expectedCount' => 0,
            ],
            'collection_availabilities' => [
                'filter' => 'collection_availabilities',
                'expectedCount' => 0,
            ],
        ];
    }
}
