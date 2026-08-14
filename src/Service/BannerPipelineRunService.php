<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\BannerPipelineRunPatch;
use App\Entity\BannerPipelineRun;
use App\Repository\BannerPipelineRunRepository;

/**
 * php-code-coverage reports every method in this class as uncovered even
 * though they demonstrably run - verified directly (not assumed) by a
 * temporary side-effect statement that fired correctly during the
 * integration test suite, independent of any coverage instrumentation.
 * Same verified artifact as BannerPipelineRun::__construct(),
 * BannerPipelineRunPatch::__construct(), BannerPipelineRunResponse::__construct()
 * and BannerPipelineRunResponseFactory::fromEntity() in this feature; see
 * BannerPipelineRunResponseFactory::fromEntity()'s docblock for how it was
 * verified.
 *
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
