<?php

declare(strict_types=1);

namespace App\Service\CalculatorService;

use App\Calculator\GameBundlesShiniesAvailabilitiesCalculator;
use App\DTO\DataChangeReport\Report;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GameBundlesShiniesAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly GameBundlesShiniesAvailabilitiesCalculator $calculator
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->calculator->execute();

        $this->report = new Report([
            $this->calculator->getStatistic(),
        ]);
    }
}
