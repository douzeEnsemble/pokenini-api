<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;

class AlbumPokemonService
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
    ) {}

    /**
     * @return int[][]|string[][]|string[][][]
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
     * @param int[][]|string[][] &$pokemons
     *
     * @return int[][]|string[][]|string[][][]
     */
    private function explodesFlatList(array $pokemons): array
    {
        $list = [];

        foreach ($pokemons as $pokemon) {
            /** @var string */
            $gameBundleSlugs = $pokemon['game_bundle_slugs'] ?? '';

            /** @var string */
            $gameBundleSShinylugs = $pokemon['game_bundle_shiny_slugs'] ?? '';

            $pokemon['game_bundles'] = array_filter(explode(',', $gameBundleSlugs));
            $pokemon['game_bundles_shiny'] = array_filter(explode(',', $gameBundleSShinylugs));

            unset($pokemon['game_bundle_slugs'], $pokemon['game_bundle_shiny_slugs']);

            $list[] = $pokemon;
        }

        return $list;
    }
}
