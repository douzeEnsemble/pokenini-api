<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\AlbumReportStatisticResponse;
use App\DTO\Response\LocalizedAlbumCatchStateResponse;

final class AlbumReportResponseFactory
{
    public static function fromReport(Report $report): AlbumReportResponse
    {
        return self::build($report, false);
    }

    /**
     * The catch states with their name in a single language.
     */
    public static function localizedFromReport(Report $report): AlbumReportResponse
    {
        return self::build($report, true);
    }

    private static function build(Report $report, bool $localized): AlbumReportResponse
    {
        return new AlbumReportResponse(
            total: $report->total,
            totalCaught: $report->totalCaught,
            totalUncaught: $report->totalUncaught,
            detail: array_map(
                static fn (Statistic $statistic): AlbumReportStatisticResponse => self::fromStatistic($statistic, $localized),
                $report->detail,
            ),
        );
    }

    private static function fromStatistic(Statistic $statistic, bool $localized): AlbumReportStatisticResponse
    {
        if ($localized) {
            return new AlbumReportStatisticResponse(
                catchState: new LocalizedAlbumCatchStateResponse(
                    slug: $statistic->slug,
                    name: $statistic->localizedName,
                    color: $statistic->color,
                ),
                count: $statistic->count,
            );
        }

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
