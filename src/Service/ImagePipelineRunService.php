<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ImagePipelineRunPatch;
use App\Entity\ImagePipelineRun;
use App\Repository\ImagePipelineRunRepository;

class ImagePipelineRunService
{
    public function __construct(
        private readonly ImagePipelineRunRepository $repository,
    ) {}

    public function create(string $correlationId): void
    {
        $this->repository->create($correlationId);
    }

    public function updateFields(string $correlationId, ImagePipelineRunPatch $patch): void
    {
        $this->repository->updateFields($correlationId, $patch);
    }

    public function findLatest(): ?ImagePipelineRun
    {
        return $this->repository->findLatest();
    }
}
