<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Repository\DexAvailabilitiesRepository;

class DexAvailabilitiesService
{
    public function __construct(
        private readonly DexAvailabilitiesRepository $repository,
    ) {}

    /**
     * @return DexAvailability[]
     */
    public function getByDex(Dex $dex): array
    {
        return $this->repository->findBy(['dex' => $dex]);
    }
}
