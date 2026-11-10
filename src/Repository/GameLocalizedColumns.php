<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * The columns of the games which hold a name: in English and French, or in the requested locale only
 * (the French columns are then null, so that the response gives a single name).
 *
 * The queries which use the localized columns need the parameters given by LocalizedName::parameters().
 */
final class GameLocalizedColumns
{
    /**
     * The Pokémon of a round, from the table "pokemon" aliased "p".
     */
    public static function pokemon(?string $locale): string
    {
        if (null === $locale) {
            return <<<'SQL'
                p.name AS "pokemon_name",
                p.french_name AS "pokemon_french_name",
                p.simplified_name AS "pokemon_simplified_name",
                p.simplified_french_name AS "pokemon_simplified_french_name",
                p.forms_label AS "pokemon_forms_label",
                p.forms_french_label AS "pokemon_forms_french_label"
                SQL;
        }

        $name = LocalizedName::sql('p');
        $simplifiedName = LocalizedName::sql('p', 'simplified_names', 'simplified_name');
        $formsLabel = LocalizedName::sql('p', 'forms_labels', 'forms_label');

        return <<<SQL
            {$name} AS "pokemon_name",
            NULL AS "pokemon_french_name",
            {$simplifiedName} AS "pokemon_simplified_name",
            NULL AS "pokemon_simplified_french_name",
            {$formsLabel} AS "pokemon_forms_label",
            NULL AS "pokemon_forms_french_label"
            SQL;
    }

    /**
     * The dex of a game, from the table "dex" aliased "d".
     */
    public static function gameDex(?string $locale): string
    {
        if (null === $locale) {
            return <<<'SQL'
                d.name AS "dex_name",
                d.french_name AS "dex_french_name"
                SQL;
        }

        $name = LocalizedName::sql('d');

        return <<<SQL
            {$name} AS "dex_name",
            NULL AS "dex_french_name"
            SQL;
    }

    /**
     * The dex which can be played, from the tables "dex" aliased "d" and "dex_category" aliased "dc".
     */
    public static function playableDex(?string $locale): string
    {
        if (null === $locale) {
            return <<<'SQL'
                d.name AS "name",
                d.french_name AS "french_name",
                d.description AS "description",
                d.french_description AS "french_description",
                dc.name AS "category_name",
                dc.french_name AS "category_french_name"
                SQL;
        }

        $name = LocalizedName::sql('d');
        $description = LocalizedName::sql('d', 'descriptions', 'description');
        $categoryName = LocalizedName::sql('dc');

        return <<<SQL
            {$name} AS "name",
            NULL AS "french_name",
            {$description} AS "description",
            NULL AS "french_description",
            {$categoryName} AS "category_name",
            NULL AS "category_french_name"
            SQL;
    }

    /**
     * The names of the colours of a colour set, from the table "pokemon_color_set" aliased "pcs".
     */
    public static function colorSet(?string $locale): string
    {
        if (null === $locale) {
            return <<<'SQL'
                pcs.names AS "names",
                pcs.french_names AS "french_names"
                SQL;
        }

        $names = LocalizedName::sql('pcs', 'localized_names', 'names');

        return <<<SQL
            {$names} AS "names",
            NULL AS "french_names"
            SQL;
    }

    /**
     * The parameters of the localized columns, none for the columns in both languages.
     *
     * @return array<string, string>
     */
    public static function parameters(?string $locale, string $fallbackLocale): array
    {
        return null === $locale ? [] : LocalizedName::parameters($locale, $fallbackLocale);
    }
}
