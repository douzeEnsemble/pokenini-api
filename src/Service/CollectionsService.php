<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CollectionsRepository;

class CollectionsService
{
    public function __construct(
        private readonly CollectionsRepository $repository,
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
