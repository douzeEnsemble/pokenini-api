<?php

namespace App\Service\CalculatorService;

use App\Calculator\DexAvailabilityCalculator;
use App\DTO\DataChangeReport\Report;
use Symfony\Contracts\Cache\CacheInterface;

class DexAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly DexAvailabilityCalculator $dexAvailabilityCalculator,
        private readonly CacheInterface $cache
    ) {
    }

    public function execute(): void
    {
        $this->cache->clear();

        $this->dexAvailabilityCalculator->execute();

        $this->report = new Report([
            $this->dexAvailabilityCalculator->getStatistic(),
        ]);
    }
}
