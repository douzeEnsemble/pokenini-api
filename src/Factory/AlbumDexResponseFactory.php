<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\LocalizedAlbumDexResponse;
use App\DTO\Response\LocalizedAlbumRegionResponse;

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

        return new AlbumDexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            flags: self::buildFlags($row),
            displayTemplate: (string) $displayTemplate,
            region: self::buildRegion($row),
            selectionRule: (string) $selectionRule,
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            version: (string) $version,
        );
    }

    /**
     * The dex with its name, its description and its region in a single language.
     *
     * @param array<array-key, mixed> $row
     */
    public static function localizedFromSqlRow(array $row): LocalizedAlbumDexResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $originalSlug */
        $originalSlug = $row['original_slug'];

        /** @var scalar $displayTemplate */
        $displayTemplate = $row['display_template'];

        /** @var scalar $selectionRule */
        $selectionRule = $row['selection_rule'];

        /** @var scalar $version */
        $version = $row['version'];

        return new LocalizedAlbumDexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: self::text($row, 'localized_name'),
            flags: self::buildFlags($row),
            displayTemplate: (string) $displayTemplate,
            region: self::buildLocalizedRegion($row),
            selectionRule: (string) $selectionRule,
            description: self::text($row, 'localized_description'),
            version: (string) $version,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildFlags(array $row): DexFlagsResponse
    {
        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isPrivate */
        $isPrivate = $row['is_private'];

        /** @var scalar $isOnHome */
        $isOnHome = $row['is_on_home'];

        /** @var scalar $isOnHub */
        $isOnHub = $row['is_on_hub'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $isCustom */
        $isCustom = $row['is_custom'];

        return new DexFlagsResponse(
            isShiny: (bool) $isShiny,
            isPrivate: (bool) $isPrivate,
            isOnHome: (bool) $isOnHome,
            isDisplayForm: (bool) $isDisplayForm,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            isCustom: (bool) $isCustom,
            isOnHub: (bool) $isOnHub,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildLocalizedRegion(array $row): ?LocalizedAlbumRegionResponse
    {
        if (empty($row['region_name'])) {
            return null;
        }

        return new LocalizedAlbumRegionResponse(name: self::text($row, 'region_localized_name'));
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

    /**
     * @param array<array-key, mixed> $row
     */
    private static function text(array $row, string $key): string
    {
        /** @var scalar $value */
        $value = $row[$key];

        return (string) $value;
    }
}
