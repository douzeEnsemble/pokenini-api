<?php

namespace App\DTO\Report;

class Report
{
    /**
     * @param Statistic[] $detail
     */
    public function __construct(
        public int $total,
        public int $totalCaught,
        public int $totalUncaught,
        public array $detail
    ) {
    }
}
