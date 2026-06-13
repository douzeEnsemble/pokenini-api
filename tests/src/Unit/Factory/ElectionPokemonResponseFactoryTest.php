<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\TypeResponse;
use App\Factory\ElectionPokemonResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
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
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('bulbasaur', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('redgreenblueyellow', $response->pokemon->originalGameBundle->slug);
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

        self::assertNull($response->forms);
    }

    #[Test]
    public function fromSqlRowWithCategoryFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'category_form_slug' => 'starter',
            'category_form_name' => 'Starter',
            'category_form_french_name' => 'Partant',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertInstanceOf(FormResponse::class, $response->forms->category);
        self::assertSame('starter', $response->forms->category->slug);
        self::assertSame('Starter', $response->forms->category->name);
        self::assertSame('Partant', $response->forms->category->frenchName);
        self::assertNull($response->forms->regional);
        self::assertNull($response->forms->special);
        self::assertNull($response->forms->variant);
    }

    #[Test]
    public function fromSqlRowWithRegionalFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'regional_form_slug' => 'alolan',
            'regional_form_name' => 'Alolan',
            'regional_form_french_name' => 'Alolan FR',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertInstanceOf(FormResponse::class, $response->forms->regional);
        self::assertSame('alolan', $response->forms->regional->slug);
        self::assertSame('Alolan', $response->forms->regional->name);
        self::assertSame('Alolan FR', $response->forms->regional->frenchName);
        self::assertNull($response->forms->special);
        self::assertNull($response->forms->variant);
    }

    #[Test]
    public function fromSqlRowWithSpecialFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'special_form_slug' => 'mega',
            'special_form_name' => 'Mega',
            'special_form_french_name' => 'Méga',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertNull($response->forms->regional);
        self::assertInstanceOf(FormResponse::class, $response->forms->special);
        self::assertSame('mega', $response->forms->special->slug);
        self::assertSame('Mega', $response->forms->special->name);
        self::assertSame('Méga', $response->forms->special->frenchName);
        self::assertNull($response->forms->variant);
    }

    #[Test]
    public function fromSqlRowWithVariantFormBuildsFormsResponse(): void
    {
        $row = $this->buildRow([
            'variant_form_slug' => 'shiny',
            'variant_form_name' => 'Shiny',
            'variant_form_french_name' => 'Chromatique',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertNull($response->forms->regional);
        self::assertNull($response->forms->special);
        self::assertInstanceOf(FormResponse::class, $response->forms->variant);
        self::assertSame('shiny', $response->forms->variant->slug);
        self::assertSame('Shiny', $response->forms->variant->name);
        self::assertSame('Chromatique', $response->forms->variant->frenchName);
    }

    #[Test]
    public function fromSqlRowWithPrimaryTypeReturnsPrimaryTypeResponse(): void
    {
        $row = $this->buildRow();

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypeResponse::class, $response->types->primary);
        self::assertSame('grass', $response->types->primary->slug);
        self::assertSame('Grass', $response->types->primary->name);
        self::assertSame('Plante', $response->types->primary->frenchName);
        self::assertSame('#78C850', $response->types->primary->color);
        self::assertNull($response->types->secondary);
    }

    #[Test]
    public function fromSqlRowWithSecondaryTypeReturnsSecondaryTypeResponse(): void
    {
        $row = $this->buildRow([
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'secondary_type_color' => '#A040A0',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(TypeResponse::class, $response->types->secondary);
        self::assertSame('poison', $response->types->secondary->slug);
        self::assertSame('Poison', $response->types->secondary->name);
        self::assertSame('Poison', $response->types->secondary->frenchName);
        self::assertSame('#A040A0', $response->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowCastsColorToString(): void
    {
        $row = $this->buildRow([
            'primary_type_color' => 42,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNotNull($response->types->primary);
        self::assertSame('42', $response->types->primary->color);
    }

    #[Test]
    public function fromSqlRowWithNoPrimaryTypeReturnsBothTypesNull(): void
    {
        $row = $this->buildRow([
            'primary_type_slug' => null,
            'primary_type_name' => null,
            'primary_type_french_name' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertNull($response->types->primary);
        self::assertNull($response->types->secondary);
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
        self::assertContainsOnlyInstancesOf(ElectionPokemonResponse::class, $responses);
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
        self::assertNull($response->pokemon->familyLead);
        self::assertNull($response->pokemon->originalGameBundle);
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
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('55', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('12', $response->pokemon->originalGameBundle->slug);
    }

    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreNull(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreEmptyString(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => '',
            'game_bundle_shiny_slugs' => '',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesFromCommaSeparatedSlugs(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
            'game_bundle_shiny_slugs' => null,
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertCount(2, $response->pokemon->gameBundles);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles[0]);
        self::assertSame('redgreenblueyellow', $response->pokemon->gameBundles[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles[1]);
        self::assertSame('goldsilvercrystal', $response->pokemon->gameBundles[1]->slug);
        self::assertSame([], $response->pokemon->gameBundlesShiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesShinyFromCommaSeparatedSlugs(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => 'redgreenblueyellow,goldsilvercrystal',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles);
        self::assertCount(2, $response->pokemon->gameBundlesShiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundlesShiny[0]);
        self::assertSame('redgreenblueyellow', $response->pokemon->gameBundlesShiny[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundlesShiny[1]);
        self::assertSame('goldsilvercrystal', $response->pokemon->gameBundlesShiny[1]->slug);
    }

    #[Test]
    public function fromSqlRowReindexesGameBundleArrayKeysAfterFilteringEmptySlugs(): void
    {
        $row = $this->buildRow([
            'game_bundle_slugs' => ',redgreenblueyellow',
            'game_bundle_shiny_slugs' => ',goldsilvercrystal',
        ]);

        $response = ElectionPokemonResponseFactory::fromSqlRow($row);

        self::assertCount(1, $response->pokemon->gameBundles);
        self::assertArrayHasKey(0, $response->pokemon->gameBundles);
        self::assertSame('redgreenblueyellow', $response->pokemon->gameBundles[0]->slug);
        self::assertCount(1, $response->pokemon->gameBundlesShiny);
        self::assertArrayHasKey(0, $response->pokemon->gameBundlesShiny);
        self::assertSame('goldsilvercrystal', $response->pokemon->gameBundlesShiny[0]->slug);
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
            'primary_type_color' => '#78C850',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'original_game_bundle_slug' => 'redgreenblueyellow',
            'pokemon_order_number' => '9999-0001-000',
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ], $overrides);
    }
}
