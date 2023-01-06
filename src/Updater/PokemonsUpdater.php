<?php

declare(strict_types=1);

namespace App\Updater;

use Symfony\Component\Uid\Uuid;

class PokemonsUpdater extends AbstractUpdater
{
    protected string $sheetName = 'Pokémons';
    protected string $tableName = 'pokemon';
    protected string $statisticName = 'pokemons';
    protected string $headerCellsRange = 'A1:AR1';
    /** @var string[] */
    protected array $recordsCellsRanges = ['A2:AR'];

    protected function getExpectedHeader(): array
    {
        return [
            'Bankable',
            'Breeedable Form',
            '#Origin',
            '#Games First Appears On',
            '#Form variant',
            '#Regional form',
            '#Special form',
            '#Category form',
            'Family',
            'Family order',
            'Evolution',
            'Pokémon Nom Complet',
            'Pokémon Nom simplifié',
            'Forme',
            'Pokémon Nom Complet Fr',
            'Pokémon Nom simplifié Fr',
            'Forme Fr',
            'Dex',
            'Sprites',
            'Sprites url',
            'Shiny Sprites',
            'Shiny Sprites url',
            'Type 1',
            'Type 1 ico',
            'Type 2',
            'Type 2 ico',
            'Steps',
            'Egg Group',
            'Egg Group 2',
            'Male',
            'Female',
            'Ability 1',
            'Ability 2',
            'Hidden Ability',
            'Abilities',
            'All Moves',
            'Move Type',
            'Natures',
            'Increases',
            'Decreases',
            'Icon',
            'Bulbapedia Name',
            'Bankable-ish',
            'Slug',
        ];
    }

    protected function upsertRecord(array $record): void
    {
        if (empty($record) || empty($record['Slug'])) {
            return;
        }

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
            family_id,
            family_order,
            prime_name,
            bankable,
            bankableish,
            original_game_bundle_id,
            variant_form_id,
            regional_form_id,
            special_form_id,
            category_form_id,
            icon_name,
            regular_sprite_url,
            shiny_sprite_url,
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
            (SELECT id FROM pokemon WHERE name = :family and :name <> :family),
            :familyOrder,
            :primeName,
            :bankable,
            :bankableish,
            (SELECT id FROM game_bundle WHERE slug = :originalGameBundle),
            (SELECT id FROM variant_form WHERE slug = :variantForm),
            (SELECT id FROM regional_form WHERE slug = :regionalForm),
            (SELECT id FROM special_form WHERE slug = :specialForm),
            (SELECT id FROM category_form WHERE slug = :categoryForm),
            :iconName,
            :regular_sprite_url,
            :shiny_sprite_url,
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
            family_id = excluded.family_id,
            family_order = excluded.family_order,
            prime_name = excluded.prime_name,
            bankable = excluded.bankable,
            bankableish = excluded.bankableish,
            original_game_bundle_id = excluded.original_game_bundle_id,
            variant_form_id = excluded.variant_form_id,
            regional_form_id = excluded.regional_form_id,
            special_form_id = excluded.special_form_id,
            category_form_id = excluded.category_form_id,
            icon_name = excluded.icon_name,
            regular_sprite_url = excluded.regular_sprite_url,
            shiny_sprite_url = excluded.shiny_sprite_url,
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
            'primeName' => (string) $pokemon['primeName'],
            'bankable' => (int) $pokemon['bankable'],
            'bankableish' => (int) $pokemon['bankableish'],
            'originalGameBundle' => (string) $pokemon['originalGameBundle'],
            'variantForm' => (string) $pokemon['variantForm'],
            'regionalForm' => (string) $pokemon['regionalForm'],
            'specialForm' => (string) $pokemon['specialForm'],
            'categoryForm' => (string) $pokemon['categoryForm'],
            'iconName' => (string) $pokemon['iconName'],
            'regular_sprite_url' => (string) $pokemon['regular_sprite_url'],
            'shiny_sprite_url' => (string) $pokemon['shiny_sprite_url'],
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
            'family' => $record['Family'],
            'familyOrder' => $record['Family order'],
            'primeName' => $record['Bulbapedia Name'],
            'bankable' => $isBankable,
            'bankableish' => $isBankableish,
            'originalGameBundle' => $record['#Games First Appears On'],
            'variantForm' => $record['#Form variant'],
            'regionalForm' => $record['#Regional form'],
            'specialForm' => $record['#Special form'],
            'categoryForm' => $record['#Category form'],
            'iconName' => $record['Icon'],
            'regular_sprite_url' => $record['Sprites url'],
            'shiny_sprite_url' => $record['Shiny Sprites url'],
            'slug' => $record['Slug'],
        ];
    }
}
