<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

class PokemonsUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Pokémons';
    protected string $tableName = 'pokemon';
    protected string $statisticName = 'pokemons';
    protected string $headerCellsRange = 'A1:AC1';
    /** @var string[] */
    protected array $recordsCellsRanges = ['A2:AC'];

    protected function getExpectedHeader(): array
    {
        return [
            'Bankable',
            'Breeedable Form',
            'Bankable-ish',
            '#Origin',
            '#Games First Appears On',
            '#Form variant',
            '#Regional form',
            '#Special form',
            '#Category form',
            '#Family',
            'Family order',
            'Slug',
            'Pokémon Nom Complet',
            'Pokémon Nom simplifié',
            'Forme',
            'Pokémon Nom Complet Fr',
            'Pokémon Nom simplifié Fr',
            'Forme Fr',
            'Dex',
            'Sprites',
            'Shiny Sprites',
            'Icon',
            'Sprites url',
            'Shiny Sprites url',
            '#Type 1',
            '#Type 2',
            'Species number',
            'PokemonDB icon name',
            'MBCMechachu sprites index',
        ];
    }

    protected function upsertRecord(array $record): void
    {
        $newRecord = $this->transformRecord($record);

        $sqlParameters = $this->getSqlParametersFromPokemon($newRecord);

        $sql = <<<SQL
        INSERT INTO pokemon (
            id,
            name,
            simplified_name,
            forms_label,
            french_name,
            simplified_french_name,
            forms_french_label,
            national_dex_number,
            family,
            family_order,
            bankable,
            bankableish,
            original_game_bundle_id,
            variant_form_id,
            regional_form_id,
            special_form_id,
            category_form_id,
            primary_type_id,
            secondary_type_id,
            icon_name,
            slug
        )
        VALUES (
            :id,
            :name,
            :simplifiedName,
            :formsLabel,
            :frenchName,
            :simplifiedFrenchName,
            :formsFrenchLabel,
            :nationalDexNumber,
            :family,
            :familyOrder,
            :bankable,
            :bankableish,
            (SELECT id FROM game_bundle WHERE slug = :originalGameBundle),
            (SELECT id FROM variant_form WHERE slug = :variantForm),
            (SELECT id FROM regional_form WHERE slug = :regionalForm),
            (SELECT id FROM special_form WHERE slug = :specialForm),
            (SELECT id FROM category_form WHERE slug = :categoryForm),
            (SELECT id FROM "type" WHERE slug = :primaryType),
            (SELECT id FROM "type" WHERE slug = :secondaryType),
            :iconName,
            :slug
        )
        ON CONFLICT (slug)
        DO
        UPDATE
        SET name = excluded.name,
            simplified_name = excluded.simplified_name,
            forms_label = excluded.forms_label,
            french_name = excluded.french_name,
            simplified_french_name = excluded.simplified_french_name,
            forms_french_label = excluded.forms_french_label,
            national_dex_number = excluded.national_dex_number,
            family = excluded.family,
            family_order = excluded.family_order,
            bankable = excluded.bankable,
            bankableish = excluded.bankableish,
            original_game_bundle_id = excluded.original_game_bundle_id,
            variant_form_id = excluded.variant_form_id,
            regional_form_id = excluded.regional_form_id,
            special_form_id = excluded.special_form_id,
            category_form_id = excluded.category_form_id,
            primary_type_id = excluded.primary_type_id,
            secondary_type_id = excluded.secondary_type_id,
            icon_name = excluded.icon_name,
            deleted_at = NULL
SQL;

        $this->executeQuery($sql, $sqlParameters);

        $this->statictic->increment();
    }

    /**
     * @param string[]|int[]|bool[] $pokemon
     *
     * @return string[]|int[]
     */
    private function getSqlParametersFromPokemon(array $pokemon): array
    {
        return [
            'id' => (string) Uuid::v4(),
            'name' => (string) $pokemon['name'],
            'simplifiedName' => (string) $pokemon['simplifiedName'],
            'formsLabel' => (string) $pokemon['formsLabel'],
            'frenchName' => (string) $pokemon['frenchName'],
            'simplifiedFrenchName' => (string) $pokemon['simplifiedFrenchName'],
            'formsFrenchLabel' => (string) $pokemon['formsFrenchLabel'],
            'nationalDexNumber' => (int) $pokemon['nationalDexNumber'],
            'family' => (string) $pokemon['family'],
            'familyOrder' => (string) $pokemon['familyOrder'],
            'bankable' => (int) $pokemon['bankable'],
            'bankableish' => (int) $pokemon['bankableish'],
            'originalGameBundle' => (string) $pokemon['originalGameBundle'],
            'variantForm' => (string) $pokemon['variantForm'],
            'regionalForm' => (string) $pokemon['regionalForm'],
            'specialForm' => (string) $pokemon['specialForm'],
            'categoryForm' => (string) $pokemon['categoryForm'],
            'primaryType' => (string) $pokemon['primaryType'],
            'secondaryType' => (string) $pokemon['secondaryType'],
            'iconName' => (string) $pokemon['iconName'],
            'slug' => (string) $pokemon['slug'],
        ];
    }

    /**
     * @param string[] $record
     *
     * @return string[]|int[]|bool[]
     */
    private function transformRecord(array $record): array
    {
        /** @var bool $isBankable */
        $isBankable = filter_var($record['Bankable'], FILTER_VALIDATE_BOOLEAN);
        /** @var bool $isBankableish */
        $isBankableish = filter_var($record['Bankable-ish'], FILTER_VALIDATE_BOOLEAN);

        return [
            'name' => $record['Pokémon Nom Complet'],
            'simplifiedName' => $record['Pokémon Nom simplifié'],
            'formsLabel' => $record['Forme'],
            'frenchName' => $record['Pokémon Nom Complet Fr'],
            'simplifiedFrenchName' => $record['Pokémon Nom simplifié Fr'],
            'formsFrenchLabel' => $record['Forme Fr'],
            'nationalDexNumber' => (int) $record['Dex'],
            'family' => $record['#Family'],
            'familyOrder' => $record['Family order'],
            'bankable' => $isBankable,
            'bankableish' => $isBankableish,
            'originalGameBundle' => $record['#Games First Appears On'],
            'variantForm' => $record['#Form variant'],
            'regionalForm' => $record['#Regional form'],
            'specialForm' => $record['#Special form'],
            'categoryForm' => $record['#Category form'],
            'primaryType' => $record['#Type 1'],
            'secondaryType' => $record['#Type 2'],
            'iconName' => $record['Icon'],
            'slug' => $record['Slug'],
        ];
    }
}
