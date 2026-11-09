<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\Repository\DexRepository;

class AlbumDexService
{
    public function __construct(
        private readonly DexRepository $dexRepository,
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @return bool[]|string[]
     */
    public function get(string $trainerExternalId, string $dexSlug, ?string $locale = null): array
    {
        return $this->dexRepository->getData(
            $trainerExternalId,
            $dexSlug,
            $locale ?? $this->fallbackLocale,
            $this->fallbackLocale,
        );
    }
}
