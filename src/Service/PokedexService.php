<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokedexRepository;

class PokedexService
{
    public function __construct(
        private readonly PokedexRepository $repository,
        private readonly PropagateCatchStateService $propagateCatchStateService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function getCatchStateCountsDefinedByTrainer(): array
    {
        return $this->repository->getCatchStateCountsDefinedByTrainer();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDexUsage(): array
    {
        return $this->repository->getDexUsage();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getCatchStateUsage(): array
    {
        return $this->repository->getCatchStateUsage();
    }

    /**
     * @return list<string>
     */
    public function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $catchStateSlug,
    ): array {
        $changedTrainerDexId = $this->repository->upsert(
            $trainerExternalId,
            $dexSlug,
            $pokemonSlug,
            $catchStateSlug,
        );

        $updatedDexSlugs = [$dexSlug];

        if (null === $changedTrainerDexId) {
            return $updatedDexSlugs;
        }

        return array_merge(
            $updatedDexSlugs,
            $this->propagateCatchStateService->propagate(
                $trainerExternalId,
                $changedTrainerDexId,
                $pokemonSlug,
                $catchStateSlug,
            ),
        );
    }
}
