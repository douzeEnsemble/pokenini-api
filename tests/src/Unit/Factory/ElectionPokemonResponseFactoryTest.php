<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\FormResponse;
use App\Factory\ElectionPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonResponseFactory::class)]
final class ElectionPokemonResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowReturnsPokemonDataResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('bulbasaur', $response->pokemon->slug);
        self::assertSame('Bulbasaur', $response->pokemon->name);
        self::assertSame('Bulbizarre', $response->pokemon->frenchName);
        self::assertSame(1, $response->pokemon->nationalDexNumber);
        self::assertNull($response->pokemon->regionalDexNumber);
        self::assertSame('Bulbasaur', $response->pokemon->simplifiedName);
        self::assertSame('', $response->pokemon->formsLabel);
        self::assertSame('Bulbizarre', $response->pokemon->simplifiedFrenchName);
        self::assertSame('', $response->pokemon->formsFrenchLabel);
        self::assertSame('bulbasaur', $response->pokemon->icon);
        self::assertSame(0, $response->pokemon->familyOrder);
        self::assertSame('bulbasaur', $response->pokemon->familyLeadSlug);
        self::assertSame('redgreenblueyellow', $response->pokemon->originalGameBundleSlug);
        self::assertSame('9999-0001-000', $response->pokemon->orderNumber);
        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowPokemonDataTypesAreCastToCorrectTypes(): void
    {
        $row = $this->buildRow([
            'pokemon_slug' => 1,
            'pokemon_name' => 2,
            'pokemon_french_name' => 3,
            'pokemon_national_dex_number' => '42',
            'pokemon_family_order' => '5',
            'pokemon_order_number' => 99,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('1', $response->pokemon->slug);
        self::assertSame('2', $response->pokemon->name);
        self::assertSame('3', $response->pokemon->frenchName);
        self::assertSame(42, $response->pokemon->nationalDexNumber);
        self::assertSame(5, $response->pokemon->familyOrder);
        self::assertSame('99', $response->pokemon->orderNumber);
    }

    #[Test]
    public function fromSqlRowWithNoFormsReturnsNullForms(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRowWithCategoryFormReturnsCategoryFormResponse(): void
    {
        $row = $this->buildRow([
            'category_form_slug' => 'starter',
            'category_form_name' => 'Starter',
            'category_form_french_name' => 'Partant',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormResponse::class, $response->categoryForm);
        self::assertSame('starter', $response->categoryForm->slug);
        self::assertSame('Starter', $response->categoryForm->name);
        self::assertSame('Partant', $response->categoryForm->frenchName);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRowWithRegionalFormReturnsRegionalFormResponse(): void
    {
        $row = $this->buildRow([
            'regional_form_slug' => 'alolan',
            'regional_form_name' => 'Alolan',
            'regional_form_french_name' => 'Alolan FR',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertInstanceOf(FormResponse::class, $response->regionalForm);
        self::assertSame('alolan', $response->regionalForm->slug);
        self::assertSame('Alolan', $response->regionalForm->name);
        self::assertSame('Alolan FR', $response->regionalForm->frenchName);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRowWithSpecialFormReturnsSpecialFormResponse(): void
    {
        $row = $this->buildRow([
            'special_form_slug' => 'mega',
            'special_form_name' => 'Mega',
            'special_form_french_name' => 'Méga',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertInstanceOf(FormResponse::class, $response->specialForm);
        self::assertSame('mega', $response->specialForm->slug);
        self::assertSame('Mega', $response->specialForm->name);
        self::assertSame('Méga', $response->specialForm->frenchName);
        self::assertNull($response->variantForm);
    }

    #[Test]
    public function fromSqlRowWithVariantFormReturnsVariantFormResponse(): void
    {
        $row = $this->buildRow([
            'variant_form_slug' => 'shiny',
            'variant_form_name' => 'Shiny',
            'variant_form_french_name' => 'Chromatique',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertInstanceOf(FormResponse::class, $response->variantForm);
        self::assertSame('shiny', $response->variantForm->slug);
        self::assertSame('Shiny', $response->variantForm->name);
        self::assertSame('Chromatique', $response->variantForm->frenchName);
    }

    #[Test]
    public function fromSqlRowWithPrimaryTypeReturnsPrimaryTypeResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $response->primaryType);
        self::assertSame('grass', $response->primaryType->slug);
        self::assertSame('Grass', $response->primaryType->name);
        self::assertSame('Plante', $response->primaryType->frenchName);
        self::assertNull($response->secondaryType);
    }

    #[Test]
    public function fromSqlRowWithSecondaryTypeReturnsSecondaryTypeResponse(): void
    {
        $row = $this->buildRow([
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $response->secondaryType);
        self::assertSame('poison', $response->secondaryType->slug);
        self::assertSame('Poison', $response->secondaryType->name);
        self::assertSame('Poison', $response->secondaryType->frenchName);
    }

    #[Test]
    public function fromSqlRowWithNoPrimaryTypeReturnsNullTypes(): void
    {
        $row = $this->buildRow([
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->primaryType);
        self::assertNull($response->secondaryType);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $rows = [
            $this->buildRow(['pokemon_slug' => 'bulbasaur']),
            $this->buildRow(['pokemon_slug' => 'charmander', 'pokemon_national_dex_number' => 4]),
        ];

        $responses = ElectionPokemonResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertSame('bulbasaur', $responses[0]->pokemon->slug);
        self::assertSame('charmander', $responses[1]->pokemon->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = ElectionPokemonResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }

    #[Test]
    public function fromElectionPokemonsListBuildsList(): void
    {
        $rows = [
            $this->buildRow(['pokemon_slug' => 'bulbasaur']),
            $this->buildRow(['pokemon_slug' => 'charmander', 'pokemon_national_dex_number' => 4]),
        ];
        $list = new ElectionPokemonsList('pick', $rows);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        self::assertSame('pick', $response->type);
        self::assertCount(2, $response->items);
        self::assertSame('bulbasaur', $response->items[0]->pokemon->slug);
        self::assertSame('charmander', $response->items[1]->pokemon->slug);
    }

    #[Test]
    public function fromElectionPokemonsListPreservesListType(): void
    {
        $list = new ElectionPokemonsList('vote', []);

        $response = ElectionPokemonResponseFactory::fromElectionPokemonsList($list);

        self::assertSame('vote', $response->type);
        self::assertCount(0, $response->items);
    }

    #[Test]
    public function fromSqlRowWithNullOptionalPokemonFieldsReturnsNulls(): void
    {
        $row = $this->buildRow([
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => null,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->simplifiedName);
        self::assertNull($response->pokemon->formsLabel);
        self::assertNull($response->pokemon->simplifiedFrenchName);
        self::assertNull($response->pokemon->formsFrenchLabel);
        self::assertNull($response->pokemon->icon);
        self::assertNull($response->pokemon->familyLeadSlug);
        self::assertNull($response->pokemon->originalGameBundleSlug);
    }

    #[Test]
    public function fromSqlRowCastsNullableStringFieldsFromNonStringValues(): void
    {
        $row = $this->buildRow([
            'pokemon_simplified_name' => 42,
            'pokemon_forms_label' => 7,
            'pokemon_simplified_french_name' => 99,
            'pokemon_forms_french_label' => 3,
            'pokemon_icon' => 1,
            'family_lead_slug' => 55,
            'original_game_bundle_slug' => 12,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('42', $response->pokemon->simplifiedName);
        self::assertSame('7', $response->pokemon->formsLabel);
        self::assertSame('99', $response->pokemon->simplifiedFrenchName);
        self::assertSame('3', $response->pokemon->formsFrenchLabel);
        self::assertSame('1', $response->pokemon->icon);
        self::assertSame('55', $response->pokemon->familyLeadSlug);
        self::assertSame('12', $response->pokemon->originalGameBundleSlug);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function buildRow(array $overrides = []): array
    {
        return array_merge([
            'pokemon_slug' => 'bulbasaur',
            'pokemon_name' => 'Bulbasaur',
            'pokemon_french_name' => 'Bulbizarre',
            'pokemon_national_dex_number' => 1,
            'pokemon_simplified_name' => 'Bulbasaur',
            'pokemon_forms_label' => '',
            'pokemon_simplified_french_name' => 'Bulbizarre',
            'pokemon_forms_french_label' => '',
            'pokemon_icon' => 'bulbasaur',
            'pokemon_family_order' => 0,
            'family_lead_slug' => 'bulbasaur',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'pokemon_order_number' => '9999-0001-000',
        ], $overrides);
    }
}
