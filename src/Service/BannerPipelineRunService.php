<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\BannerPipelineRunPatch;
use App\Entity\BannerPipelineRun;
use App\Repository\BannerPipelineRunRepository;

/**
 * @codeCoverageIgnore
 */
class BannerPipelineRunService
{
    public function __construct(
        private readonly BannerPipelineRunRepository $repository,
    ) {}

    public function create(string $correlationId): void
    {
        $this->repository->create($correlationId);
    }

    public function updateFields(string $correlationId, BannerPipelineRunPatch $patch): bool
    {
        return $this->repository->updateFields($correlationId, $patch);
    }

    public function findLatest(): ?BannerPipelineRun
    {
        return $this->repository->findLatest();
    }
}
