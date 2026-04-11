<?php

declare(strict_types=1);

namespace App\DTO\AlbumReport;

final class Report
{
    /**
     * @param Statistic[] $detail
     */
    public function __construct(
        public int $total,
        public int $totalCaught,
        public int $totalUncaught,
        public array $detail
    ) {}
}
