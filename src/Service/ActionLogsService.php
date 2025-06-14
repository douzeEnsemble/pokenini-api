<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ActionLogsRepository;

class ActionLogsService
{
    public function __construct(
        private readonly ActionLogsRepository $repository,
    ) {}

    /**
     * @return null[][]|string[][]
     */
    public function getLastests(): array
    {
        return $this->repository->getLastests();
    }
}
