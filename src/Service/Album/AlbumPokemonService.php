<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;

/**
 * @psalm-import-type PokedexRepositoryItems from \App\Tests\Common\Types\PokedexTypes
 * @psalm-import-type PokedexResponseItems from \App\Tests\Common\Types\PokedexTypes
 */
class AlbumPokemonService
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
    ) {}

    /**
     * @return PokedexResponseItems
     */
    public function get(string $trainerExternalId, string $dexSlug, AlbumFilters $albumFilters): array
    {
        $pokemons = $this->pokedexRepository->getList(
            $trainerExternalId,
            $dexSlug,
            $albumFilters,
        );

        return $this->explodesFlatList($pokemons);
    }

    /**
     * @param PokedexRepositoryItems &$pokemons
     *
     * @return PokedexResponseItems
     */
    private function explodesFlatList(array $pokemons): array
    {
        $list = [];

        foreach ($pokemons as $pokemon) {
            $gameBundleSlugs = $pokemon['game_bundle_slugs'] ?? '';

            $gameBundleSShinylugs = $pokemon['game_bundle_shiny_slugs'] ?? '';

            $pokemon['game_bundles'] = array_filter(explode(',', $gameBundleSlugs));
            $pokemon['game_bundles_shiny'] = array_filter(explode(',', $gameBundleSShinylugs));

            unset($pokemon['game_bundle_slugs'], $pokemon['game_bundle_shiny_slugs']);

            $list[] = $pokemon;
        }

        return $list;
    }
}
