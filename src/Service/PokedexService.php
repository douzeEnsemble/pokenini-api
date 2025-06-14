<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokedexRepository;

class PokedexService
{
    public function __construct(
        private readonly PokedexRepository $repository,
    ) {}

    /**
     * @return int[]|string[]
     */
    public function getCatchStateCountsDefinedByTrainer(): array
    {
        return $this->repository->getCatchStateCountsDefinedByTrainer();
    }

    /**
     * @return int[]|string[]
     */
    public function getDexUsage(): array
    {
        return $this->repository->getDexUsage();
    }

    /**
     * @return int[]|string[]
     */
    public function getCatchStateUsage(): array
    {
        return $this->repository->getCatchStateUsage();
    }

    public function upsert(
        string $trainerExternalId,
        string $dexSlug,
        string $pokemonSlug,
        string $catchStateSlug,
    ): void {
        $this->repository->upsert(
            $trainerExternalId,
            $dexSlug,
            $pokemonSlug,
            $catchStateSlug,
        );
    }
}
