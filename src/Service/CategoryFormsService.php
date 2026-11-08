<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CategoryFormsRepository;

class CategoryFormsService
{
    public function __construct(
        private readonly CategoryFormsRepository $repository,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @return string[][]
     */
    public function getAll(?string $locale = null): array
    {
        return $this->repository->getAll($locale ?? $this->fallbackLocale, $this->fallbackLocale);
    }
}
