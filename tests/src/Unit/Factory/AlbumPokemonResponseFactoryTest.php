<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\Factory\AlbumPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 */
#[CoversClass(AlbumPokemonResponseFactory::class)]
final class AlbumPokemonResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowBuildsPokemonSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertSame('bulbasaur', $result->pokemon->slug);
        self::assertSame('Bulbasaur', $result->pokemon->name);
        self::assertSame('Bulbizarre', $result->pokemon->frenchName);
        self::assertSame(1, $result->pokemon->nationalDexNumber);
        self::assertSame(1, $result->pokemon->regionalDexNumber);
        self::assertSame('Bulbasaur', $result->pokemon->simplifiedName);
        self::assertSame('', $result->pokemon->formsLabel);
        self::assertSame('Bulbizarre', $result->pokemon->simplifiedFrenchName);
        self::assertSame('', $result->pokemon->formsFrenchLabel);
        self::assertSame('bulbasaur', $result->pokemon->icon);
        self::assertSame(0, $result->pokemon->familyOrder);
        self::assertSame('bulbasaur', $result->pokemon->familyLeadSlug);
        self::assertSame('redgreenblueyellow', $result->pokemon->originalGameBundleSlug);
        self::assertSame('0001-0001-000', $result->pokemon->orderNumber);
        self::assertSame(['redgreenblueyellow', 'goldsilvercrystal'], $result->pokemon->gameBundles);
        self::assertSame(['redgreenblueyellow'], $result->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsCatchStateSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumCatchStateResponse::class, $result->catchState);
        self::assertSame('no', $result->catchState->slug);
        self::assertSame('No', $result->catchState->name);
        self::assertSame('Non', $result->catchState->frenchName);
    }

    #[Test]
    public function fromSqlRowSetsNullCatchStateWhenNotSet(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->catchState);
    }

    #[Test]
    public function fromSqlRowBuildsCategoryFormSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumFormResponse::class, $result->categoryForm);
        self::assertSame('starter', $result->categoryForm->slug);
        self::assertSame('Starter', $result->categoryForm->name);
    }

    #[Test]
    public function fromSqlRowSetsNullFormsWhenNotSet(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertNull($result->regionalForm);
        self::assertNull($result->specialForm);
        self::assertNull($result->variantForm);
    }

    #[Test]
    public function fromSqlRowBuildsSpecialFormSubObject(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['special_form_slug'] = 'mega';
        $row['special_form_name'] = 'Mega';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($result->categoryForm);
        self::assertInstanceOf(AlbumFormResponse::class, $result->specialForm);
        self::assertSame('mega', $result->specialForm->slug);
        self::assertSame('Mega', $result->specialForm->name);
    }

    #[Test]
    public function fromSqlRowBuildsVariantFormSubObject(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['variant_form_slug'] = 'gender';
        $row['variant_form_name'] = 'Gender';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormResponse::class, $result->variantForm);
        self::assertSame('gender', $result->variantForm->slug);
        self::assertSame('Gender', $result->variantForm->name);
    }

    #[Test]
    public function fromSqlRowBuildsRegionalFormSubObject(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['regional_form_slug'] = 'alolan';
        $row['regional_form_name'] = 'Alolan';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormResponse::class, $result->regionalForm);
        self::assertSame('alolan', $result->regionalForm->slug);
        self::assertSame('Alolan', $result->regionalForm->name);
    }

    #[Test]
    public function fromSqlRowBuildsPrimaryTypeSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumTypeResponse::class, $result->primaryType);
        self::assertSame('grass', $result->primaryType->slug);
        self::assertSame('Grass', $result->primaryType->name);
        self::assertSame('Plante', $result->primaryType->frenchName);
    }

    #[Test]
    public function fromSqlRowBuildsSecondaryTypeSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumTypeResponse::class, $result->secondaryType);
        self::assertSame('poison', $result->secondaryType->slug);
        self::assertSame('Poison', $result->secondaryType->name);
        self::assertSame('Poison', $result->secondaryType->frenchName);
    }

    #[Test]
    public function fromSqlRowSetsNullPrimaryTypeWhenAbsent(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->primaryType);
        self::assertNull($result->secondaryType);
    }

    #[Test]
    public function fromSqlRowSetsNullSecondaryTypeForSingleTypePokemon(): void
    {
        $row = $this->getBulbasaurRow();
        $row['secondary_type_slug'] = null;
        $row['secondary_type_name'] = null;
        $row['secondary_type_french_name'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $result->primaryType);
        self::assertNull($result->secondaryType);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionalDexNumber(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->pokemon->regionalDexNumber);
    }

    #[Test]
    public function fromSqlRowCastsNullGameBundlesToEmptyArray(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundles'] = null;
        $row['game_bundles_shiny'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $result->pokemon->gameBundles);
        self::assertSame([], $result->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowCastsNullableStringFieldsToStrings(): void
    {
        $row = $this->getBulbasaurRow();
        $row['pokemon_simplified_name'] = 42;
        $row['pokemon_forms_label'] = 1;
        $row['pokemon_simplified_french_name'] = 99;
        $row['pokemon_forms_french_label'] = 0;
        $row['pokemon_icon'] = 7;
        $row['family_lead_slug'] = 123;
        $row['original_game_bundle_slug'] = 456;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame('42', $result->pokemon->simplifiedName);
        self::assertSame('1', $result->pokemon->formsLabel);
        self::assertSame('99', $result->pokemon->simplifiedFrenchName);
        self::assertSame('0', $result->pokemon->formsFrenchLabel);
        self::assertSame('7', $result->pokemon->icon);
        self::assertSame('123', $result->pokemon->familyLeadSlug);
        self::assertSame('456', $result->pokemon->originalGameBundleSlug);
    }

    #[Test]
    public function fromSqlRowCastsNumericFieldsToCorrectTypes(): void
    {
        $row = $this->getBulbasaurRow();
        $row['pokemon_national_dex_number'] = '1';
        $row['pokemon_regional_dex_number'] = '1';
        $row['pokemon_family_order'] = '0';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame(1, $result->pokemon->nationalDexNumber);
        self::assertSame(1, $result->pokemon->regionalDexNumber);
        self::assertSame(0, $result->pokemon->familyOrder);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRows(): void
    {
        $rows = [$this->getBulbasaurRow(), $this->getDouzeRow()];

        $results = AlbumPokemonResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(AlbumPokemonResponse::class, $results);
        self::assertSame('bulbasaur', $results[0]->pokemon->slug);
        self::assertSame('douze', $results[1]->pokemon->slug);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $results = AlbumPokemonResponseFactory::fromSqlRows([]);

        self::assertCount(0, $results);
    }

    /**
     * @return array<string, mixed>
     */
    private function getBulbasaurRow(): array
    {
        return [
            'pokemon_national_dex_number' => 1,
            'pokemon_regional_dex_number' => 1,
            'pokemon_order_number' => '0001-0001-000',
            'pokemon_slug' => 'bulbasaur',
            'pokemon_name' => 'Bulbasaur',
            'pokemon_simplified_name' => 'Bulbasaur',
            'pokemon_forms_label' => '',
            'pokemon_french_name' => 'Bulbizarre',
            'pokemon_simplified_french_name' => 'Bulbizarre',
            'pokemon_forms_french_label' => '',
            'pokemon_icon' => 'bulbasaur',
            'category_form_slug' => 'starter',
            'category_form_name' => 'Starter',
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'catch_state_slug' => 'no',
            'catch_state_name' => 'No',
            'catch_state_french_name' => 'Non',
            'family_lead_slug' => 'bulbasaur',
            'pokemon_family_order' => 0,
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'game_bundles' => ['redgreenblueyellow', 'goldsilvercrystal'],
            'game_bundles_shiny' => ['redgreenblueyellow'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDouzeRow(): array
    {
        return [
            'pokemon_national_dex_number' => 9912,
            'pokemon_regional_dex_number' => null,
            'pokemon_order_number' => '9999-9912-000',
            'pokemon_slug' => 'douze',
            'pokemon_name' => 'Douze',
            'pokemon_simplified_name' => 'Douze',
            'pokemon_forms_label' => '',
            'pokemon_french_name' => 'Douze',
            'pokemon_simplified_french_name' => 'Douze',
            'pokemon_forms_french_label' => '',
            'pokemon_icon' => 'douze',
            'category_form_slug' => null,
            'category_form_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'catch_state_slug' => null,
            'catch_state_name' => null,
            'catch_state_french_name' => null,
            'family_lead_slug' => 'douze',
            'pokemon_family_order' => 0,
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'game_bundles' => ['un', 'dos', 'tres'],
            'game_bundles_shiny' => [],
        ];
    }
}
