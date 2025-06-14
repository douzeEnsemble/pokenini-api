<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\DexQueryOptions;
use App\Repository\DexRepository;

class DexCanHoldElectionService
{
    public function __construct(
        private readonly DexRepository $dexRepository,
    ) {}

    /**
     * @return bool[][]|int[][]|string[][]
     */
    public function get(DexQueryOptions $dexQueryOptions): array
    {
        return $this->dexRepository->getCanHoldElection($dexQueryOptions);
    }
}
