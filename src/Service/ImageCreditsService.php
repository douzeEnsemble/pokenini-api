<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokemonImageCreditRepository;

class ImageCreditsService
{
    public function __construct(
        private readonly PokemonImageCreditRepository $repository,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @return array<array{
     *   pokemon_slug: string,
     *   pokemon_name: string,
     *   pokemon_french_name: string,
     *   localized_name: string,
     *   pokemon_icon: string,
     *   small_regular_credit: ?string,
     *   small_shiny_credit: ?string,
     *   big_regular_credit: ?string,
     *   big_shiny_credit: ?string,
     * }>
     */
    public function getAllByPokemon(?string $locale = null): array
    {
        return $this->repository->findAllPokemonWithCredits($locale ?? $this->fallbackLocale, $this->fallbackLocale);
    }
}
