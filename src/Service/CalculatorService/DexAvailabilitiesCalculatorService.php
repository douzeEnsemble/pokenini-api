<?php

namespace App\Service\CalculatorService;

use App\Calculator\DexAvailabilitiesCalculator;
use App\DTO\DataChangeReport\Report;
use Symfony\Contracts\Cache\CacheInterface;

class DexAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly DexAvailabilitiesCalculator $calculator,
        private readonly CacheInterface $cache
    ) {
    }

    public function execute(): void
    {
        $this->cache->clear();

        $this->calculator->execute();

        $this->report = new Report([
            $this->calculator->getStatistic(),
        ]);
    }
}
