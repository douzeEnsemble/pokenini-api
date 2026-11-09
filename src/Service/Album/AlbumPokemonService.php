<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;

/**
 * @psalm-import-type PokedexRepositoryItems from \App\Tests\Common\Types\PokedexTypes
 */
class AlbumPokemonService
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @return PokedexRepositoryItems
     */
    public function get(string $trainerExternalId, string $dexSlug, AlbumFilters $albumFilters, ?string $locale = null): array
    {
        return $this->pokedexRepository->getList(
            $trainerExternalId,
            $dexSlug,
            $albumFilters,
            $locale ?? $this->fallbackLocale,
            $this->fallbackLocale,
        );
    }
}
