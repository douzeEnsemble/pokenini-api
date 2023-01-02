<?php

namespace App\Service\CalculatorService;

use App\DTO\DataChangeReport\Report;

interface CalculatorServiceInterface
{
    public function execute(): void;

    public function getReport(): Report;
}
