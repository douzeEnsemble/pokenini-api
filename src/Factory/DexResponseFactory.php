<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionReport\Report;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexResponse;

final class DexResponseFactory
{
    private const array EMPTY_METRICS = [
        'view_count_sum' => 0,
        'win_count_sum' => 0,
        'view_count_max' => 0,
        'win_count_max' => 0,
        'under_max_view_count' => 0,
        'max_view_count' => 0,
        'dex_total_count' => 0,
    ];

    /**
     * Transform a single SQL row into DexResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row, Report $report): DexResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $originalSlug */
        $originalSlug = $row['original_slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $row['dex_total_count'];

        return new DexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            flags: new DexFlagsResponse(
                isShiny: (bool) $isShiny,
                isPrivate: (bool) $isPrivate,
                isOnHome: (bool) $isOnHome,
                isDisplayForm: (bool) $isDisplayForm,
                isReleased: (bool) $isReleased,
                isPremium: (bool) $isPremium,
                isCustom: (bool) $isCustom,
            ),
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            dexTotalCount: (int) $dexTotalCount,
            report: ElectionReportResponseFactory::fromReport($report),
        );
    }

    /**
     * Transform multiple SQL rows into DexResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, Report>                     $reports keyed by dex slug
     *
     * @return DexResponse[]
     */
    public static function fromSqlRows(array $rows, array $reports): array
    {
        return array_map(
            static fn (array $row): DexResponse => self::fromSqlRow(
                $row,
                $reports[(string) $row['slug']] ?? new Report([], self::EMPTY_METRICS),
            ),
            $rows,
        );
    }
}
