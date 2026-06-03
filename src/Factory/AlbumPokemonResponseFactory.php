<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\PokemonDataResponse;

final class AlbumPokemonResponseFactory
{
    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): AlbumPokemonResponse
    {
        return new AlbumPokemonResponse(
            pokemon: self::buildPokemon($row),
            catchState: self::buildCatchState($row),
            categoryForm: self::buildForm('category_form', $row),
            regionalForm: self::buildForm('regional_form', $row),
            specialForm: self::buildForm('special_form', $row),
            variantForm: self::buildForm('variant_form', $row),
            primaryType: self::buildType('primary_type', $row),
            secondaryType: self::buildType('secondary_type', $row),
        );
    }

    /**
     * @param array<array<string, mixed>> $rows
     *
     * @return AlbumPokemonResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildPokemon(array $row): PokemonDataResponse
    {
        /** @var scalar $slug */
        $slug = $row['pokemon_slug'];

        /** @var scalar $name */
        $name = $row['pokemon_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['pokemon_french_name'];

        /** @var scalar $nationalDexNumber */
        $nationalDexNumber = $row['pokemon_national_dex_number'];

        /** @var null|scalar $regionalDexNumber */
        $regionalDexNumber = $row['pokemon_regional_dex_number'] ?? null;

        /** @var null|scalar $simplifiedName */
        $simplifiedName = $row['pokemon_simplified_name'] ?? null;

        /** @var null|scalar $formsLabel */
        $formsLabel = $row['pokemon_forms_label'] ?? null;

        /** @var null|scalar $simplifiedFrenchName */
        $simplifiedFrenchName = $row['pokemon_simplified_french_name'] ?? null;

        /** @var null|scalar $formsFrenchLabel */
        $formsFrenchLabel = $row['pokemon_forms_french_label'] ?? null;

        /** @var null|scalar $icon */
        $icon = $row['pokemon_icon'] ?? null;

        /** @var scalar $familyOrder */
        $familyOrder = $row['pokemon_family_order'];

        /** @var null|scalar $familyLeadSlug */
        $familyLeadSlug = $row['family_lead_slug'] ?? null;

        /** @var null|scalar $originalGameBundleSlug */
        $originalGameBundleSlug = $row['original_game_bundle_slug'] ?? null;

        /** @var scalar $orderNumber */
        $orderNumber = $row['pokemon_order_number'];

        /** @var array<string> $gameBundles */
        $gameBundles = (array) $row['game_bundles'];

        /** @var array<string> $gameBundlesShiny */
        $gameBundlesShiny = (array) $row['game_bundles_shiny'];

        return new PokemonDataResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            nationalDexNumber: (int) $nationalDexNumber,
            regionalDexNumber: null !== $regionalDexNumber ? (int) $regionalDexNumber : null,
            simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
            formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
            simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
            formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
            icon: null !== $icon ? (string) $icon : null,
            familyOrder: (int) $familyOrder,
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
            orderNumber: (string) $orderNumber,
            gameBundles: $gameBundles,
            gameBundlesShiny: $gameBundlesShiny,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildCatchState(array $row): ?AlbumCatchStateResponse
    {
        if (empty($row['catch_state_slug'])) {
            return null;
        }

        /** @var scalar $slug */
        $slug = $row['catch_state_slug'];

        /** @var scalar $name */
        $name = $row['catch_state_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['catch_state_french_name'];

        return new AlbumCatchStateResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForm(string $prefix, array $row): ?AlbumFormResponse
    {
        $slugKey = "{$prefix}_slug";
        $nameKey = "{$prefix}_name";

        if (empty($row[$slugKey])) {
            return null;
        }

        /** @var scalar $slug */
        $slug = $row[$slugKey];

        /** @var scalar $name */
        $name = $row[$nameKey];

        return new AlbumFormResponse(
            slug: (string) $slug,
            name: (string) $name,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildType(string $prefix, array $row): ?AlbumTypeResponse
    {
        $slugKey = "{$prefix}_slug";
        $nameKey = "{$prefix}_name";
        $frenchNameKey = "{$prefix}_french_name";

        if (empty($row[$slugKey])) {
            return null;
        }

        /** @var scalar $slug */
        $slug = $row[$slugKey];

        /** @var scalar $name */
        $name = $row[$nameKey];

        /** @var scalar $frenchName */
        $frenchName = $row[$frenchNameKey];

        return new AlbumTypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }
}
