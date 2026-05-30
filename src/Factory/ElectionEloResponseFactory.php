<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;

final class ElectionEloResponseFactory
{
    /**
     * Transform a single SQL row into ElectionEloResponse DTO.
     *
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): ElectionEloResponse
    {
        /** @var scalar $elo */
        $elo = $row['elo'];

        /** @var scalar $significance */
        $significance = $row['significance'];

        return new ElectionEloResponse(
            pokemon: self::buildPokemonData($row),
            forms: self::buildForms($row),
            types: self::buildTypes($row),
            elo: (float) $elo,
            significance: (bool) $significance,
        );
    }

    /**
     * Transform multiple SQL rows into ElectionEloResponse DTOs.
     *
     * @param array<array<string, mixed>> $rows
     *
     * @return ElectionEloResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildPokemonData(array $row): PokemonDataResponse
    {
        /** @var scalar $slug */
        $slug = $row['pokemon_slug'];

        /** @var scalar $name */
        $name = $row['pokemon_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['pokemon_french_name'];

        /** @var scalar $nationalDexNumber */
        $nationalDexNumber = $row['pokemon_national_dex_number'];

        /** @var null|scalar $simplifiedName */
        $simplifiedName = $row['pokemon_simplified_name'];

        /** @var null|scalar $formsLabel */
        $formsLabel = $row['pokemon_forms_label'];

        /** @var null|scalar $simplifiedFrenchName */
        $simplifiedFrenchName = $row['pokemon_simplified_french_name'];

        /** @var null|scalar $formsFrenchLabel */
        $formsFrenchLabel = $row['pokemon_forms_french_label'];

        /** @var null|scalar $icon */
        $icon = $row['pokemon_icon'];

        /** @var scalar $familyOrder */
        $familyOrder = $row['pokemon_family_order'];

        /** @var null|scalar $familyLeadSlug */
        $familyLeadSlug = $row['family_lead_slug'];

        /** @var null|scalar $originalGameBundleSlug */
        $originalGameBundleSlug = $row['original_game_bundle_slug'];

        /** @var scalar $orderNumber */
        $orderNumber = $row['pokemon_order_number'];

        return new PokemonDataResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            nationalDexNumber: (int) $nationalDexNumber,
            simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
            formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
            simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
            formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
            icon: null !== $icon ? (string) $icon : null,
            familyOrder: (int) $familyOrder,
            familyLeadSlug: null !== $familyLeadSlug ? (string) $familyLeadSlug : null,
            originalGameBundleSlug: null !== $originalGameBundleSlug ? (string) $originalGameBundleSlug : null,
            orderNumber: (string) $orderNumber,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForms(array $row): ?FormsResponse
    {
        $hasAnyForm = !empty($row['category_form_slug'])
            || !empty($row['regional_form_slug'])
            || !empty($row['special_form_slug'])
            || !empty($row['variant_form_slug']);

        if (!$hasAnyForm) {
            return null;
        }

        return new FormsResponse(
            category: self::buildForm('category_form', $row),
            regional: self::buildForm('regional_form', $row),
            special: self::buildForm('special_form', $row),
            variant: self::buildForm('variant_form', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForm(string $prefix, array $row): ?FormResponse
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

        return new FormResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: '',
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildTypes(array $row): TypesResponse
    {
        return new TypesResponse(
            primary: self::buildType('primary_type', $row),
            secondary: self::buildType('secondary_type', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildType(string $prefix, array $row): ?TypeResponse
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

        return new TypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: '',
        );
    }
}
