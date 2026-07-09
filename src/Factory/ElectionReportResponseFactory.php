<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\ElectionReportResponse;

final class ElectionReportResponseFactory
{
    public static function fromReport(Report $report): ElectionReportResponse
    {
        return new ElectionReportResponse(
            top: ElectionEloResponseFactory::fromSqlRows($report->top),
            metrics: ElectionMetricsResponseFactory::fromArray($report->metrics),
        );
    }
}
