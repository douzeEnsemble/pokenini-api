<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\VariantFormsRepository;

class VariantFormsService
{
    public function __construct(
        private readonly VariantFormsRepository $repository,
    ) {}

    /**
     * @return string[][]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}
