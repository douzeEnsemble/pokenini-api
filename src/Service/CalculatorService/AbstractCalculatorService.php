<?php

declare(strict_types=1);

namespace App\Service\CalculatorService;

use App\DTO\DataChangeReport\Report;

abstract class AbstractCalculatorService implements CalculatorServiceInterface
{
    protected Report $report;

    public function getReport(): Report
    {
        return $this->report;
    }
}
