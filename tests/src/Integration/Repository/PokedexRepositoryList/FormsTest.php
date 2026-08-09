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
final class FormsTest extends KernelTestCase
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
    public function getListQueryCategoryForm(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'category_forms' => [
                    'starter',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'bulbasaur',
                'charmander',
            ],
        );
    }

    #[Test]
    public function getListQueryRegionalForm(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'regional_forms' => [
                    'alolan',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'rattata-alola',
                'raticate-alola',
                'raticate-alola-totem',
            ],
        );
    }

    #[Test]
    public function getListQuerySpecialForm(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'special_forms' => [
                    'gigantamax',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'venusaur-gmax',
                'butterfree-gmax',
            ],
        );
    }

    #[Test]
    public function getListQuerySpecialsForm(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'special_forms' => [
                    'gigantamax',
                    'mega',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'venusaur-mega',
                'venusaur-gmax',
                'butterfree-gmax',
            ],
        );
    }

    #[Test]
    public function getListQueryVariantForm(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $pokedex = $repo->getList(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([
                'variant_forms' => [
                    'gender',
                ],
            ])
        );

        $this->assertSameSlugs(
            $pokedex,
            [
                'venusaur-f',
                'butterfree-f',
                'rattata-f',
                'raticate-f',
            ],
        );
    }
}
