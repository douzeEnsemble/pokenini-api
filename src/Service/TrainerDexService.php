<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\DexQueryOptions;
use App\DTO\TrainerDexAttributes;
use App\Repository\TrainerDexRepository;

class TrainerDexService
{
    public function __construct(
        private readonly TrainerDexRepository $repository,
    ) {}

    public function insertIfNeeded(
        string $trainerExternalId,
        string $dexSlug,
    ): void {
        $this->repository->insertIfNeeded($trainerExternalId, $dexSlug);
    }

    /**
     * @return \Traversable<int, array<mixed, mixed>>
     */
    public function getListQuery(
        string $trainerExternalId,
        DexQueryOptions $options,
    ): \Traversable {
        return $this->repository->getListQuery($trainerExternalId, $options);
    }

    public function set(
        string $trainerExternalId,
        string $dexSlug,
        TrainerDexAttributes $attributes
    ): void {
        $this->repository->set($trainerExternalId, $dexSlug, $attributes);
    }
}
