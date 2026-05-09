<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Dex;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\DexRepository;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DexAvailabilitiesCalculator extends AbstractCalculator
{
    protected string $statisticName = 'dex_availabilities';

    public function __construct(
        private readonly DexAvailabilitiesRepository $dexAvailabilitiesRepo,
        private readonly DexRepository $dexRepository,
        private readonly DexAvailabilityCalculator $dexAvailabilityCalculator,
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->init();

        $this->dexAvailabilitiesRepo->removeAll();

        $dexQuery = $this->dexRepository->getQueryAll();

        /** @var Dex $dex */
        foreach ($dexQuery->toIterable() as $dex) {
            $count = $this->dexAvailabilityCalculator->calculate($dex);

            $this->statistic->incrementBy($count);
        }
    }
}
