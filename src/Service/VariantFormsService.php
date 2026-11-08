<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\VariantFormsRepository;

class VariantFormsService
{
    public function __construct(
        private readonly VariantFormsRepository $repository,
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
