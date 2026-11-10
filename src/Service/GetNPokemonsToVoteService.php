<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Repository\PokemonsRepository;

class GetNPokemonsToVoteService
{
    public function __construct(
        private readonly PokemonsRepository $pokemonsRepository,
        private readonly int $eloDefault,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @param null|string $locale the language of the names, null for the legacy ones (English and French)
     *
     * @return array<array<string, mixed>>
     */
    public function getNPokemonsToVote(TrainerPokemonEloListQueryOptions $queryOptions, ?string $locale = null): array
    {
        return $this->pokemonsRepository->getNToVote(
            $queryOptions->dexSlug,
            $queryOptions->count,
            $queryOptions->trainerExternalId,
            $queryOptions->electionSlug,
            $queryOptions->albumFilters,
            $this->eloDefault,
            $locale ?? $this->fallbackLocale,
            $this->fallbackLocale,
        );
    }
}
