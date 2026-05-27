<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TypesRepository;

class TypesService
{
    public function __construct(
        private readonly TypesRepository $repository,
    ) {}

    /**
     * @return array<array-key, string>[]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}
