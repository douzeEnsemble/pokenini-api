<?php

namespace App\Service\CalculatorService;

use App\Calculator\GameBundleAvailabilityCalculator;
use App\DTO\DataChangeReport\Report;

class GameBundleAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly GameBundleAvailabilityCalculator $gameBundleAvailabilityCalculator
    ) {
    }

    public function execute(): void
    {
        $this->gameBundleAvailabilityCalculator->execute();

        $this->report = new Report([
            $this->gameBundleAvailabilityCalculator->getStatistic(),
        ]);
    }
}
