<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\ElectionReportResponse;

/**
 * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
 */
final class ElectionReportResponseFactory
{
    public static function fromReport(Report $report, bool $localized = false): ElectionReportResponse
    {
        return new ElectionReportResponse(
            top: ElectionEloResponseFactory::fromSqlRows($report->top, $localized),
            metrics: ElectionMetricsResponseFactory::fromArray($report->metrics),
        );
    }
}
