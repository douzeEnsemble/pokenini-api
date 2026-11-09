<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\ImageCreditResponse;
use App\DTO\Response\LocalizedAlbumCatchStateResponse;
use App\DTO\Response\LocalizedAlbumFormResponse;
use App\DTO\Response\LocalizedAlbumTypeResponse;
use App\DTO\Response\LocalizedPokemonLabelsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonLabelsResponse;
use App\DTO\Response\PokemonSlugResponse;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class AlbumPokemonResponseFactory
{
    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row): AlbumPokemonResponse
    {
        return self::build($row, false);
    }

    /**
     * The names of the Pokémon in every language, the others in a single language.
     *
     * @param array<string, mixed> $row
     */
    public static function localizedFromSqlRow(array $row): AlbumPokemonResponse
    {
        return self::build($row, true);
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
     * @param array<array<string, mixed>> $rows
     *
     * @return AlbumPokemonResponse[]
     */
    public static function localizedFromSqlRows(array $rows): array
    {
        return array_map(self::localizedFromSqlRow(...), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function build(array $row, bool $localized): AlbumPokemonResponse
    {
        return new AlbumPokemonResponse(
            pokemon: self::buildPokemon($row, $localized),
            catchState: self::buildCatchState($row, $localized),
            forms: self::buildForms($row, $localized),
            types: self::buildTypes($row, $localized),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildPokemon(array $row, bool $localized): PokemonDataResponse
    {
        /** @var scalar $slug */
        $slug = $row['pokemon_slug'];

        /** @var scalar $name */
        $name = $row['pokemon_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['pokemon_french_name'];

        /** @var null|scalar $simplifiedName */
        $simplifiedName = $row['pokemon_simplified_name'] ?? null;

        /** @var null|scalar $formsLabel */
        $formsLabel = $row['pokemon_forms_label'] ?? null;

        /** @var null|scalar $simplifiedFrenchName */
        $simplifiedFrenchName = $row['pokemon_simplified_french_name'] ?? null;

        /** @var null|scalar $formsFrenchLabel */
        $formsFrenchLabel = $row['pokemon_forms_french_label'] ?? null;

        /** @var scalar $nationalDexNumber */
        $nationalDexNumber = $row['pokemon_national_dex_number'];

        /** @var null|scalar $regionalDexNumber */
        $regionalDexNumber = $row['pokemon_regional_dex_number'] ?? null;

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
            labels: $localized
                ? self::buildLocalizedLabels($row)
                : new PokemonLabelsResponse(
                    name: (string) $name,
                    frenchName: (string) $frenchName,
                    simplifiedName: null !== $simplifiedName ? (string) $simplifiedName : null,
                    simplifiedFrenchName: null !== $simplifiedFrenchName ? (string) $simplifiedFrenchName : null,
                    formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
                    formsFrenchLabel: null !== $formsFrenchLabel ? (string) $formsFrenchLabel : null,
                ),
            nationalDexNumber: (int) $nationalDexNumber,
            regionalDexNumber: null !== $regionalDexNumber ? (int) $regionalDexNumber : null,
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
    private static function buildLocalizedLabels(array $row): LocalizedPokemonLabelsResponse
    {
        /** @var null|scalar $formsLabel */
        $formsLabel = $row['pokemon_localized_forms_label'] ?? null;

        return new LocalizedPokemonLabelsResponse(
            names: self::decodeTexts($row['pokemon_names'] ?? null),
            simplifiedNames: self::decodeTexts($row['pokemon_simplified_names'] ?? null),
            formsLabel: null !== $formsLabel ? (string) $formsLabel : null,
        );
    }

    /**
     * The texts indexed by locale, as the database gives them (a JSON object).
     *
     * @return array<array-key, string>
     */
    private static function decodeTexts(mixed $json): array
    {
        $texts = \is_string($json) ? json_decode($json, true) : null;

        if (!\is_array($texts)) {
            return [];
        }

        return array_map(static fn (mixed $text): string => \is_scalar($text) ? (string) $text : '', $texts);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function text(array $row, string $key): string
    {
        /** @var scalar $value */
        $value = $row[$key];

        return (string) $value;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildCredit(string $prefix, array $row): ?ImageCreditResponse
    {
        $key = "{$prefix}_credit_source";

        if (empty($row[$key])) {
            return null;
        }

        /** @var scalar $credit */
        $credit = $row[$key];

        return new ImageCreditResponse(credit: (string) $credit);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildCatchState(array $row, bool $localized): AlbumCatchStateResponse|LocalizedAlbumCatchStateResponse|null
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

        /** @var scalar $color */
        $color = $row['catch_state_color'];

        if ($localized) {
            return new LocalizedAlbumCatchStateResponse(
                slug: (string) $slug,
                name: self::text($row, 'catch_state_localized_name'),
                color: (string) $color,
            );
        }

        return new AlbumCatchStateResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForms(array $row, bool $localized): ?AlbumFormsResponse
    {
        $hasAnyForm = !empty($row['category_form_slug'])
            || !empty($row['regional_form_slug'])
            || !empty($row['special_form_slug'])
            || !empty($row['variant_form_slug']);

        if (!$hasAnyForm) {
            return null;
        }

        return new AlbumFormsResponse(
            category: self::buildForm('category_form', $row, $localized),
            regional: self::buildForm('regional_form', $row, $localized),
            special: self::buildForm('special_form', $row, $localized),
            variant: self::buildForm('variant_form', $row, $localized),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildForm(string $prefix, array $row, bool $localized): AlbumFormResponse|LocalizedAlbumFormResponse|null
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

        if ($localized) {
            return new LocalizedAlbumFormResponse(
                slug: (string) $slug,
                name: self::text($row, "{$prefix}_localized_name"),
            );
        }

        return new AlbumFormResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildTypes(array $row, bool $localized): AlbumTypesResponse
    {
        return new AlbumTypesResponse(
            primary: self::buildType('primary_type', $row, $localized),
            secondary: self::buildType('secondary_type', $row, $localized),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function buildType(string $prefix, array $row, bool $localized): AlbumTypeResponse|LocalizedAlbumTypeResponse|null
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

        if ($localized) {
            return new LocalizedAlbumTypeResponse(
                slug: (string) $slug,
                name: self::text($row, "{$prefix}_localized_name"),
                color: (string) $color,
            );
        }

        return new AlbumTypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }
}
