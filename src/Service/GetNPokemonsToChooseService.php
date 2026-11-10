<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ElectionPokemonsList;
use App\DTO\TrainerPokemonEloListQueryOptions;

class GetNPokemonsToChooseService
{
    public function __construct(
        private readonly GetNPokemonsToPickService $toPickService,
        private readonly GetNPokemonsToVoteService $toVoteService,
    ) {}

    /**
     * @param null|string $locale the language of the names, null for the legacy ones (English and French)
     */
    public function getNPokemonsToChoose(TrainerPokemonEloListQueryOptions $queryOptions, ?string $locale = null): ElectionPokemonsList
    {
        $toPick = $this->toPickService->getNPokemonsToPick($queryOptions, $locale);

        if (!empty($toPick)) {
            return new ElectionPokemonsList('pick', $toPick);
        }

        return new ElectionPokemonsList(
            'vote',
            $this->toVoteService->getNPokemonsToVote($queryOptions, $locale)
        );
    }
}
