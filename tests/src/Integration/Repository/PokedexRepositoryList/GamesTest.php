<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository\PokedexRepositoryList;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;
use App\Repository\Trait\FiltersTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use App\Tests\Common\Traits\PokemonListTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokedexRepository::class)]
#[CoversTrait(FiltersTrait::class)]
final class GamesTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;
    use DataTrait;
    use PokemonListTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getListQueryOriginalGameBundle(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'original_game_bundles' => [
                    'redgreenblueyellow',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
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
    public function getListQueryGameBundleAvailabilities(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'game_bundle_availabilities' => [
                    'ultrasunultramoon',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'rattata-alola',
                'raticate-alola',
            ],
        );
    }

    #[Test]
    public function getListQueryGameBundleAvailabilitiesNegative(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'game_bundle_availabilities' => [
                    '!ultrasunultramoon',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'bulbasaur',
                'ivysaur',
                'venusaur',
                'venusaur-f',
                'venusaur-mega',
                'venusaur-gmax',
                'charmander',
                'charmeleon',
                'charizard',
                'caterpie',
                'metapod',
                'butterfree',
                'butterfree-f',
                'butterfree-gmax',
                'rattata',
                'rattata-f',
                'raticate',
                'raticate-f',
                'raticate-alola-totem',
                'douze',
            ],
        );
    }

    #[Test]
    public function getListQueryGameBundleShinyAvailabilities(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'game_bundle_shiny_availabilities' => [
                    'ultrasunultramoon',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'rattata-f',
                'rattata-alola',
                'raticate',
                'raticate-f',
            ],
        );
    }

    #[Test]
    public function getListQueryGameBundleShinyAvailabilitiesNegative(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'game_bundle_shiny_availabilities' => [
                    '!ultrasunultramoon',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'bulbasaur',
                'ivysaur',
                'venusaur',
                'venusaur-f',
                'venusaur-mega',
                'venusaur-gmax',
                'charmander',
                'charmeleon',
                'charizard',
                'caterpie',
                'metapod',
                'butterfree',
                'butterfree-f',
                'butterfree-gmax',
                'rattata',
                'raticate-alola',
                'raticate-alola-totem',
                'douze',
            ],
        );
    }
}
