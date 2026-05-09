<?php

declare(strict_types=1);

namespace App\Calculator;

use App\DTO\DataChangeReport\Statistic;

abstract class AbstractCalculator implements CalculatorInterface
{
    protected string $statisticName;
    protected Statistic $statistic;

    public function init(): void
    {
        $this->statistic = new Statistic($this->statisticName);
    }

    #[\Override]
    public function getStatistic(): Statistic
    {
        return $this->statistic;
    }
}
