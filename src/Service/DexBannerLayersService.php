<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DexRepository;

class DexBannerLayersService
{
    public function __construct(
        private readonly DexRepository $repository,
    ) {}

    /**
     * @return array<string, string[]>
     */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->repository->getBannerLayers() as $row) {
            $result[$row['slug']] = array_values(array_filter(explode(',', $row['banner_layers'])));
        }

        return $result;
    }
}
