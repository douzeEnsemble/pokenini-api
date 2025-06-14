<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\RegionalFormsRepository;

class RegionalFormsService
{
    public function __construct(
        private readonly RegionalFormsRepository $repository,
    ) {}

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}
