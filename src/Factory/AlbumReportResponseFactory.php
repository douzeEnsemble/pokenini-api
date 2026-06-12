<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumReportStatisticResponse;

final class AlbumReportResponseFactory
{
    public static function fromReport(Report $report): AlbumReportResponse
    {
        return new AlbumReportResponse(
            total: $report->total,
            totalCaught: $report->totalCaught,
            totalUncaught: $report->totalUncaught,
            detail: array_map(self::fromStatistic(...), $report->detail),
        );
    }

    private static function fromStatistic(Statistic $statistic): AlbumReportStatisticResponse
    {
        return new AlbumReportStatisticResponse(
            catchState: new AlbumCatchStateResponse(
                slug: $statistic->slug,
                name: $statistic->name,
                frenchName: $statistic->frenchName,
                color: $statistic->color,
            ),
            count: $statistic->count,
        );
    }
}
