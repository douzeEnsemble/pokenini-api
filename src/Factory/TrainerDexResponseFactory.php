<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\TrainerDexResponse;

final class TrainerDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): TrainerDexResponse
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
            dexSlug: (string) $dexSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            slug: (string) $slug,
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            displayTemplate: (string) $displayTemplate,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return TrainerDexResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
