<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\GameBundlesRepository;

class GameBundlesService
{
    public function __construct(
        private readonly GameBundlesRepository $repository,
    ) {}

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}
