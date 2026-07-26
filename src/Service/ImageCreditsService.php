<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PokemonImageCreditRepository;

class ImageCreditsService
{
    public function __construct(
        private readonly PokemonImageCreditRepository $repository,
    ) {}

    /**
     * @return array<array{source: string}>
     */
    public function getAll(): array
    {
        return $this->repository->findAllDistinctSources();
    }
}
