<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\ReportTrainerResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;

final class ReportResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateCountRow(array $row): TrainerCatchStateCountResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $trainer */
        $trainer = $row['trainer'];

        return new TrainerCatchStateCountResponse(
            count: (int) $count,
            trainer: new ReportTrainerResponse(
                externalId: (string) $trainer,
            ),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromDexUsageRow(array $row): DexUsageResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new DexUsageResponse(
            count: (int) $count,
            dex: new ReportDexResponse(
                slug: (string) $slug,
                name: (string) $name,
                frenchName: (string) $frenchName,
            ),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateUsageRow(array $row): CatchStateUsageResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new CatchStateUsageResponse(
            count: (int) $count,
            catchState: new ReportCatchStateResponse(
                slug: (string) $slug,
                name: (string) $name,
                frenchName: (string) $frenchName,
                color: (string) $color,
            ),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $catchStateCounts
     * @param array<array-key, array<array-key, mixed>> $dexUsage
     * @param array<array-key, array<array-key, mixed>> $catchStateUsage
     */
    public static function fromServiceArrays(
        array $catchStateCounts,
        array $dexUsage,
        array $catchStateUsage,
    ): ReportResponse {
        return new ReportResponse(
            catchStateCountsDefinedByTrainer: array_map(self::fromCatchStateCountRow(...), $catchStateCounts),
            dexUsage: array_map(self::fromDexUsageRow(...), $dexUsage),
            catchStateUsage: array_map(self::fromCatchStateUsageRow(...), $catchStateUsage),
        );
    }
}
