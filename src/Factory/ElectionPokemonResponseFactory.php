<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\ImageCreditResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonLabelsResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class ElectionPokemonResponseFactory
{
    public static function fromElectionPokemonsList(ElectionPokemonsList $list): ElectionPokemonsListResponse
    {
        return new ElectionPokemonsListResponse(
            type: $list->getListType(),
            items: self::fromSqlRows($list->getItems()),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): ElectionPokemonResponse
    {
        return new ElectionPokemonResponse(
            pokemon: self::buildPokemon($row),
            forms: self::buildForms($row),
            types: self::buildTypes($row),
        );
    }

    /**
     * @param array<array<string, mixed>> $rows
     *
     * @return ElectionPokemonResponse[]
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

        /** @var null|scalar $gameBundleSlugsRaw */
        $gameBundleSlugsRaw = $row['game_bundle_slugs'] ?? null;

        /** @var array<string> $gameBundleSlugs */
        $gameBundleSlugs = array_values(array_filter(explode(',', (string) ($gameBundleSlugsRaw ?? ''))));

        $gameBundles = array_map(
            static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
            $gameBundleSlugs,
        );

        /** @var null|scalar $gameBundleShinySlugRaw */
        $gameBundleShinySlugRaw = $row['game_bundle_shiny_slugs'] ?? null;

        /** @var array<string> $gameBundleShinySlugs */
        $gameBundleShinySlugs = array_values(array_filter(explode(',', (string) ($gameBundleShinySlugRaw ?? ''))));

        $gameBundlesShiny = array_map(
            static fn (string $slug): GameBundleSlugResponse => new GameBundleSlugResponse(slug: $slug),
            $gameBundleShinySlugs,
        );

        return new PokemonDataResponse(
            slug: (string) $slug,
            labels: new PokemonLabelsResponse(
                name: (string) $name,
                frenchName: (string) $frenchName,
                simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
                simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
                formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
                formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
            ),
            nationalDexNumber: (int) $nationalDexNumber,
            regionalDexNumber: null,
            icon: null !== $icon ? (string) $icon : null,
            familyOrder: (int) $familyOrder,
            familyLead: null !== $familyLeadSlug
                ? new PokemonSlugResponse(slug: (string) $familyLeadSlug)
                : null,
            originalGameBundle: null !== $originalGameBundleSlug
                ? new GameBundleSlugResponse(slug: (string) $originalGameBundleSlug)
                : null,
            orderNumber: (string) $orderNumber,
            gameBundles: new GameBundlesGroupResponse(
                normal: $gameBundles,
                shiny: $gameBundlesShiny,
            ),
            smallRegularCredit: self::buildCredit('small_regular', $row),
            smallShinyCredit: self::buildCredit('small_shiny', $row),
            bigRegularCredit: self::buildCredit('big_regular', $row),
            bigShinyCredit: self::buildCredit('big_shiny', $row),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildCredit(string $prefix, array $row): ?ImageCreditResponse
    {
        $nameKey = "{$prefix}_credit_name";
        $urlKey = "{$prefix}_credit_url";

        if (empty($row[$nameKey]) || empty($row[$urlKey])) {
            return null;
        }

        /** @var scalar $name */
        $name = $row[$nameKey];

        /** @var scalar $url */
        $url = $row[$urlKey];

        return new ImageCreditResponse(name: (string) $name, url: (string) $url);
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

        return new FormResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
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
        $colorKey = "{$prefix}_color";

        if (empty($row[$slugKey])) {
            return null;
        }

        /** @var scalar $slug */
        $slug = $row[$slugKey];

        /** @var scalar $name */
        $name = $row[$nameKey];

        /** @var scalar $frenchName */
        $frenchName = $row[$frenchNameKey];

        /** @var scalar $color */
        $color = $row[$colorKey];

        return new TypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }
}
