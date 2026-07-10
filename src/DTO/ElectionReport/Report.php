<?php

declare(strict_types=1);

namespace App\DTO\ElectionReport;

final class Report
{
    /**
     * @param array<array-key, array<string, mixed>>                                                                                                                        $top
     * @param array{view_count_sum: int, win_count_sum: int, view_count_max: int, win_count_max: int, under_max_view_count: int, max_view_count: int, dex_total_count: int} $metrics
     */
    public function __construct(
        public readonly array $top,
        public readonly array $metrics,
    ) {}
}
