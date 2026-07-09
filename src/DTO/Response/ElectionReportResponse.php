<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionReportResponse
{
    /**
     * @param ElectionEloResponse[] $top
     */
    public function __construct(
        public readonly array $top,
        public readonly ElectionMetricsResponse $metrics,
    ) {}
}
