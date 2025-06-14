<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CatchStatesRepository;

class CatchStatesService
{
    public function __construct(
        private readonly CatchStatesRepository $repository,
    ) {}

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}
