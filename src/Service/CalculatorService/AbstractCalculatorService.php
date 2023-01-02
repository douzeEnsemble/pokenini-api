<?php

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
