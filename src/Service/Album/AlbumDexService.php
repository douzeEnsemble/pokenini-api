<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\Repository\DexRepository;

class AlbumDexService
{
    public function __construct(
        private readonly DexRepository $dexRepository,
    ) {}

    /**
     * @return bool[]|string[]
     */
    public function get(string $trainerExternalId, string $dexSlug): array
    {
        return $this->dexRepository->getData($trainerExternalId, $dexSlug);
    }
}
