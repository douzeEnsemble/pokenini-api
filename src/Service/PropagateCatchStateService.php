<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokedexRepository;
use App\Repository\TrainerDexLinkRepository;

class PropagateCatchStateService
{
    public function __construct(
        private readonly TrainerDexLinkRepository $trainerDexLinkRepository,
        private readonly PokedexRepository $pokedexRepository,
    ) {}

    /**
     * @return list<string>
     */
    public function propagate(
        string $trainerExternalId,
        string $originTrainerDexId,
        string $pokemonSlug,
        string $catchStateSlug,
    ): array {
        $updatedDexSlugs = [];

        $queue = $this->trainerDexLinkRepository->getOutgoingEdges($trainerExternalId, $originTrainerDexId);

        while ([] !== $queue) {
            $edge = array_shift($queue);

            $changed = $this->pokedexRepository->upsertIfDifferent(
                $edge['target_trainer_dex_id'],
                $pokemonSlug,
                $catchStateSlug,
            );

            if (!$changed) {
                continue;
            }

            $updatedDexSlugs[] = $edge['target_dex_slug'];

            $queue = array_merge(
                $queue,
                $this->trainerDexLinkRepository->getOutgoingEdges($trainerExternalId, $edge['target_trainer_dex_id']),
            );
        }

        return $updatedDexSlugs;
    }
}
