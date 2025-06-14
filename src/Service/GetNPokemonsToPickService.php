<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\TrainerPokemonEloListQueryOptions;
use App\Repository\PokemonsRepository;

class GetNPokemonsToPickService
{
    public function __construct(
        private readonly PokemonsRepository $pokemonsRepository,
        private readonly int $eloDefault,
    ) {}

    /**
     * @return string[][]
     */
    public function getNPokemonsToPick(TrainerPokemonEloListQueryOptions $queryOptions): array
    {
        return $this->pokemonsRepository->getNToPick(
            $queryOptions->dexSlug,
            $queryOptions->count,
            $queryOptions->trainerExternalId,
            $queryOptions->electionSlug,
            $queryOptions->albumFilters,
            $this->eloDefault,
        );
    }
}
