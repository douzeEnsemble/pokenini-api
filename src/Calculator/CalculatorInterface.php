<?php

declare(strict_types=1);

namespace App\Calculator;

use App\DTO\DataChangeReport\Statistic;

interface CalculatorInterface
{
    public function execute(): void;

    public function getStatistic(): Statistic;
}
