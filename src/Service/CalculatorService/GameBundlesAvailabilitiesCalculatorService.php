<?php

namespace App\Service\CalculatorService;

use App\Calculator\GameBundlesAvailabilitiesCalculator;
use App\DTO\DataChangeReport\Report;

class GameBundlesAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly GameBundlesAvailabilitiesCalculator $calculator
    ) {
    }

    public function execute(): void
    {
        $this->calculator->execute();

        $this->report = new Report([
            $this->calculator->getStatistic(),
        ]);
    }
}
