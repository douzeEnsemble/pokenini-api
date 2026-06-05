<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;

final class AlbumDexResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): AlbumDexResponse
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

        /** @var scalar $displayTemplate */
        $displayTemplate = $row['display_template'];

        /** @var scalar $selectionRule */
        $selectionRule = $row['selection_rule'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $version */
        $version = $row['version'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new AlbumDexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            displayTemplate: (string) $displayTemplate,
            region: self::buildRegion($row),
            selectionRule: (string) $selectionRule,
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            version: (string) $version,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildRegion(array $row): ?AlbumRegionResponse
    {
        if (empty($row['region_name'])) {
            return null;
        }

        /** @var scalar $regionName */
        $regionName = $row['region_name'];

        /** @var scalar $regionFrenchName */
        $regionFrenchName = $row['region_french_name'];

        return new AlbumRegionResponse(
            name: (string) $regionName,
            frenchName: (string) $regionFrenchName,
        );
    }
}
