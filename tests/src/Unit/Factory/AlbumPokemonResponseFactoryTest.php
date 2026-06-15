<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\Factory\AlbumPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.TooManyMethods")
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
        self::assertInstanceOf(PokemonSlugResponse::class, $result->pokemon->familyLead);
        self::assertSame('bulbasaur', $result->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->originalGameBundle);
        self::assertSame('redgreenblueyellow', $result->pokemon->originalGameBundle->slug);
        self::assertSame('0001-0001-000', $result->pokemon->orderNumber);
        self::assertCount(2, $result->pokemon->gameBundles->normal);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->normal[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles->normal[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->normal[1]);
        self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles->normal[1]->slug);
        self::assertCount(1, $result->pokemon->gameBundles->shiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->shiny[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles->shiny[0]->slug);
    }

    #[Test]
    public function fromSqlRowBuildsCatchStateSubObject(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumCatchStateResponse::class, $result->catchState);
        self::assertSame('no', $result->catchState->slug);
        self::assertSame('No', $result->catchState->name);
        self::assertSame('Non', $result->catchState->frenchName);
        self::assertSame('#e57373', $result->catchState->color);
    }

    #[Test]
    public function fromSqlRowSetsNullCatchStateWhenNotSet(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->catchState);
    }

    #[Test]
    public function fromSqlRowCastsCatchStateColorToString(): void
    {
        $row = $this->getBulbasaurRow();
        $row['catch_state_color'] = 0xE57373;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumCatchStateResponse::class, $result->catchState);
        self::assertSame((string) 0xE57373, $result->catchState->color);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithCategoryForm(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->category);
        self::assertSame('starter', $result->forms->category->slug);
        self::assertSame('Starter', $result->forms->category->name);
        self::assertSame('de Départ', $result->forms->category->frenchName);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->special);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromSqlRowSetsNullFormsWhenNoFormsPresent(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->forms);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithSpecialForm(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['special_form_slug'] = 'mega';
        $row['special_form_name'] = 'Mega';
        $row['special_form_french_name'] = 'Mega';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->regional);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->special);
        self::assertSame('mega', $result->forms->special->slug);
        self::assertSame('Mega', $result->forms->special->name);
        self::assertSame('Mega', $result->forms->special->frenchName);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithVariantForm(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['variant_form_slug'] = 'gender';
        $row['variant_form_name'] = 'Gender';
        $row['variant_form_french_name'] = 'Sexe';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertNull($result->forms->category);
        self::assertNull($result->forms->regional);
        self::assertNull($result->forms->special);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->variant);
        self::assertSame('gender', $result->forms->variant->slug);
        self::assertSame('Gender', $result->forms->variant->name);
        self::assertSame('Sexe', $result->forms->variant->frenchName);
    }

    #[Test]
    public function fromSqlRowBuildsFormsObjectWithRegionalForm(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_slug'] = null;
        $row['category_form_name'] = null;
        $row['regional_form_slug'] = 'alolan';
        $row['regional_form_name'] = 'Alolan';
        $row['regional_form_french_name'] = "d'Alola";

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertNull($result->forms->category);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->regional);
        self::assertSame('alolan', $result->forms->regional->slug);
        self::assertSame('Alolan', $result->forms->regional->name);
        self::assertSame("d'Alola", $result->forms->regional->frenchName);
        self::assertNull($result->forms->special);
        self::assertNull($result->forms->variant);
    }

    #[Test]
    public function fromSqlRowCastsFormFrenchNameToString(): void
    {
        $row = $this->getBulbasaurRow();
        $row['category_form_french_name'] = 42;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumFormsResponse::class, $result->forms);
        self::assertInstanceOf(AlbumFormResponse::class, $result->forms->category);
        self::assertSame('42', $result->forms->category->frenchName);
    }

    #[Test]
    public function fromSqlRowBuildsTypesObjectWithPrimaryAndSecondaryType(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getBulbasaurRow());

        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->primary);
        self::assertSame('grass', $result->types->primary->slug);
        self::assertSame('Grass', $result->types->primary->name);
        self::assertSame('Plante', $result->types->primary->frenchName);
        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->secondary);
        self::assertSame('poison', $result->types->secondary->slug);
        self::assertSame('Poison', $result->types->secondary->name);
        self::assertSame('Poison', $result->types->secondary->frenchName);
        self::assertSame('#78C850', $result->types->primary->color);
        self::assertSame('#A040A0', $result->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowBuildsTypesObjectWithNullTypesWhenAbsent(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->types->primary);
        self::assertNull($result->types->secondary);
    }

    #[Test]
    public function fromSqlRowBuildsTypesObjectWithNullSecondaryForSingleTypePokemon(): void
    {
        $row = $this->getBulbasaurRow();
        $row['secondary_type_slug'] = null;
        $row['secondary_type_name'] = null;
        $row['secondary_type_french_name'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->primary);
        self::assertNull($result->types->secondary);
    }

    #[Test]
    public function fromSqlRowCastsTypeColorToString(): void
    {
        $row = $this->getBulbasaurRow();
        $row['primary_type_color'] = 42;
        $row['secondary_type_color'] = 99;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->primary);
        self::assertInstanceOf(AlbumTypeResponse::class, $result->types->secondary);
        self::assertSame('42', $result->types->primary->color);
        self::assertSame('99', $result->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowSetsNullRegionalDexNumber(): void
    {
        $result = AlbumPokemonResponseFactory::fromSqlRow($this->getDouzeRow());

        self::assertNull($result->pokemon->regionalDexNumber);
    }

    #[Test]
    public function fromSqlRowParsesNullGameBundleSlugsAsEmptyArrayForBothFields(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundle_slugs'] = null;
        $row['game_bundle_shiny_slugs'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $result->pokemon->gameBundles->normal);
        self::assertSame([], $result->pokemon->gameBundles->shiny);
    }

    #[Test]
    public function fromSqlRowParsesEmptyGameBundleSlugsAsEmptyArray(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundle_slugs'] = '';
        $row['game_bundle_shiny_slugs'] = '';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $result->pokemon->gameBundles->normal);
        self::assertSame([], $result->pokemon->gameBundles->shiny);
    }

    #[Test]
    public function fromSqlRowParsesPopulatedGameBundleSlugs(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundle_slugs'] = 'redgreenblueyellow,goldsilvercrystal';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertCount(2, $result->pokemon->gameBundles->normal);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->normal[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles->normal[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->normal[1]);
        self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles->normal[1]->slug);
    }

    #[Test]
    public function fromSqlRowParsesPopulatedGameBundleShinySlugs(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundle_shiny_slugs'] = 'redgreenblueyellow,goldsilvercrystal';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertCount(2, $result->pokemon->gameBundles->shiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->shiny[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles->shiny[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->shiny[1]);
        self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles->shiny[1]->slug);
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
        self::assertInstanceOf(PokemonSlugResponse::class, $result->pokemon->familyLead);
        self::assertSame('123', $result->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->originalGameBundle);
        self::assertSame('456', $result->pokemon->originalGameBundle->slug);
    }

    #[Test]
    public function fromSqlRowSetsNullFamilyLeadAndOriginalGameBundleWhenNull(): void
    {
        $row = $this->getBulbasaurRow();
        $row['family_lead_slug'] = null;
        $row['original_game_bundle_slug'] = null;

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($result->pokemon->familyLead);
        self::assertNull($result->pokemon->originalGameBundle);
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
    public function fromSqlRowReindexesGameBundlesAfterFilter(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundle_slugs'] = 'redgreenblueyellow,,goldsilvercrystal';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertCount(2, $result->pokemon->gameBundles->normal);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->normal[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles->normal[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->normal[1]);
        self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles->normal[1]->slug);
    }

    #[Test]
    public function fromSqlRowReindexesGameBundleShinyAfterFilter(): void
    {
        $row = $this->getBulbasaurRow();
        $row['game_bundle_shiny_slugs'] = 'redgreenblueyellow,,goldsilvercrystal';

        $result = AlbumPokemonResponseFactory::fromSqlRow($row);

        self::assertCount(2, $result->pokemon->gameBundles->shiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->shiny[0]);
        self::assertSame('redgreenblueyellow', $result->pokemon->gameBundles->shiny[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $result->pokemon->gameBundles->shiny[1]);
        self::assertSame('goldsilvercrystal', $result->pokemon->gameBundles->shiny[1]->slug);
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
            'category_form_french_name' => 'de Départ',
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'special_form_slug' => null,
            'special_form_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'catch_state_slug' => 'no',
            'catch_state_name' => 'No',
            'catch_state_french_name' => 'Non',
            'catch_state_color' => '#e57373',
            'family_lead_slug' => 'bulbasaur',
            'pokemon_family_order' => 0,
            'primary_type_slug' => 'grass',
            'primary_type_name' => 'Grass',
            'primary_type_french_name' => 'Plante',
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'primary_type_color' => '#78C850',
            'secondary_type_color' => '#A040A0',
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
            'game_bundle_shiny_slugs' => 'redgreenblueyellow',
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
            'catch_state_color' => null,
            'family_lead_slug' => 'douze',
            'pokemon_family_order' => 0,
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'primary_type_color' => null,
            'secondary_type_color' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'game_bundle_slugs' => 'un,dos,tres',
            'game_bundle_shiny_slugs' => '',
        ];
    }
}
