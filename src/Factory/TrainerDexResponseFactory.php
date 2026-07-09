<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\AlbumReport\Report;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;
use App\DTO\Response\TrainerDexSettingsResponse;

final class TrainerDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row, Report $report): TrainerDexResponse
    {
        /** @var scalar $dexSlug */
        $dexSlug = $row['dex_slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $displayTemplate */
        $displayTemplate = $row['display_template'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new TrainerDexResponse(
            dex: new DexSlugResponse(
                slug: (string) $dexSlug,
            ),
            settings: new TrainerDexSettingsResponse(
                name: (string) $name,
                frenchName: (string) $frenchName,
                slug: (string) $slug,
                displayTemplate: (string) $displayTemplate,
            ),
            flags: new DexFlagsResponse(
                isShiny: (bool) $isShiny,
                isPrivate: (bool) $isPrivate,
                isOnHome: (bool) $isOnHome,
                isDisplayForm: (bool) $isDisplayForm,
                isReleased: (bool) $isReleased,
                isPremium: (bool) $isPremium,
                isCustom: (bool) $isCustom,
            ),
            report: AlbumReportResponseFactory::fromReport($report),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     * @param array<string, Report>                      $reports keyed by the row's effective dex slug (`slug`, not `dex_slug`)
     *
     * @return TrainerDexResponse[]
     */
    public static function fromSqlRows(array $rows, array $reports): array
    {
        return array_map(
            static fn (array $row): TrainerDexResponse => self::fromSqlRow(
                $row,
                $reports[(string) $row['slug']] ?? new Report(0, 0, 0, []),
            ),
            $rows,
        );
    }
}
