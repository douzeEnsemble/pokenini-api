<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TypesRepository;

class TypesService
{
    public function __construct(
        private readonly TypesRepository $repository,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @return array<array-key, string>[]
     */
    public function getAll(?string $locale = null): array
    {
        return $this->repository->getAll($locale ?? $this->fallbackLocale, $this->fallbackLocale);
    }
}
