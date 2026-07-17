<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\ElectionEloScoreResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\Factory\ElectionEloResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionEloResponseFactory::class)]
#[CoversClass(ElectionEloScoreResponse::class)]
final class ElectionEloResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame(1200.5, $response->score->elo);
        self::assertTrue($response->score->significance);
        self::assertSame('pikachu', $response->pokemon->slug);
        self::assertSame('Pikachu', $response->pokemon->labels->name);
        self::assertNull($response->forms);
        self::assertNotNull($response->types->primary);
        self::assertSame('electric', $response->types->primary->slug);
        self::assertSame('#FFCC33', $response->types->primary->color);
        self::assertNull($response->types->secondary);
    }

    #[Test]
    public function fromSqlRowHandlesFormsWhenPresent(): void
    {
        $row = [
            'elo' => 1500.0,
            'significance' => false,
            'pokemon_slug' => 'rotom',
            'pokemon_name' => 'Rotom',
            'pokemon_french_name' => 'Motisma',
            'pokemon_national_dex_number' => 479,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => 'Heat Rotom',
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => 'Motisma Chaleur',
            'pokemon_icon' => 'rotom.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'rotom',
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0479-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => null,
            'regional_form_name' => null,
            'regional_form_french_name' => null,
            'special_form_slug' => 'heat',
            'special_form_name' => 'Heat',
            'special_form_french_name' => 'Chaleur',
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => 'fire',
            'secondary_type_name' => 'Fire',
            'secondary_type_french_name' => 'Feu',
            'secondary_type_color' => '#F08030',
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertNull($response->forms->regional);
        self::assertInstanceOf(FormResponse::class, $response->forms->special);
        self::assertSame('heat', $response->forms->special->slug);
        self::assertSame('Heat', $response->forms->special->name);
        self::assertSame('Chaleur', $response->forms->special->frenchName);
        self::assertNull($response->forms->variant);
        self::assertNotNull($response->types->secondary);
        self::assertSame('fire', $response->types->secondary->slug);
        self::assertNotNull($response->types->primary);
        self::assertSame('#FFCC33', $response->types->primary->color);
        self::assertSame('#F08030', $response->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowCastsBooleanSignificanceCorrectly(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => 0,
            'pokemon_slug' => 'bulbasaur',
            'pokemon_name' => 'Bulbasaur',
            'pokemon_french_name' => 'Bulbizarre',
            'pokemon_national_dex_number' => 1,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'bulbasaur.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'bulbasaur',
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0001-001',
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
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
            'secondary_type_color' => '#A040A0',
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertFalse($response->score->significance);
        self::assertNotNull($response->types->primary);
        self::assertSame('#78C850', $response->types->primary->color);
        self::assertNotNull($response->types->secondary);
        self::assertSame('#A040A0', $response->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'elo' => 1200.0,
                'significance' => true,
                'pokemon_slug' => 'pikachu',
                'pokemon_name' => 'Pikachu',
                'pokemon_french_name' => 'Pikachu',
                'pokemon_national_dex_number' => 25,
                'pokemon_simplified_name' => null,
                'pokemon_forms_label' => null,
                'pokemon_simplified_french_name' => null,
                'pokemon_forms_french_label' => null,
                'pokemon_icon' => 'pikachu.png',
                'pokemon_family_order' => 1,
                'family_lead_slug' => 'pichu',
                'original_game_bundle_slug' => null,
                'pokemon_order_number' => '9999-0025-001',
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
                'primary_type_slug' => 'electric',
                'primary_type_name' => 'Electric',
                'primary_type_french_name' => 'Électrique',
                'primary_type_color' => '#FFCC33',
                'secondary_type_slug' => null,
                'secondary_type_name' => null,
                'secondary_type_french_name' => null,
                'secondary_type_color' => null,
                'game_bundle_slugs' => null,
                'game_bundle_shiny_slugs' => null,
            ],
            [
                'elo' => 1150.0,
                'significance' => false,
                'pokemon_slug' => 'charizard',
                'pokemon_name' => 'Charizard',
                'pokemon_french_name' => 'Dracaufeu',
                'pokemon_national_dex_number' => 6,
                'pokemon_simplified_name' => null,
                'pokemon_forms_label' => null,
                'pokemon_simplified_french_name' => null,
                'pokemon_forms_french_label' => null,
                'pokemon_icon' => 'charizard.png',
                'pokemon_family_order' => 3,
                'family_lead_slug' => 'charmander',
                'original_game_bundle_slug' => null,
                'pokemon_order_number' => '9999-0006-001',
                'category_form_slug' => null,
                'category_form_name' => null,
                'category_form_french_name' => null,
                'regional_form_slug' => 'galar',
                'regional_form_name' => 'Galar',
                'regional_form_french_name' => 'Forme de Galar',
                'special_form_slug' => null,
                'special_form_name' => null,
                'special_form_french_name' => null,
                'variant_form_slug' => null,
                'variant_form_name' => null,
                'variant_form_french_name' => null,
                'primary_type_slug' => 'fire',
                'primary_type_name' => 'Fire',
                'primary_type_french_name' => 'Feu',
                'primary_type_color' => '#F08030',
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
                'secondary_type_color' => '#A890F0',
                'game_bundle_slugs' => null,
                'game_bundle_shiny_slugs' => null,
            ],
        ];

        $responses = ElectionEloResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ElectionEloResponse::class, $responses);
        self::assertSame('pikachu', $responses[0]->pokemon->slug);
        self::assertSame('charizard', $responses[1]->pokemon->slug);
        self::assertSame(1200.0, $responses[0]->score->elo);
        self::assertSame(1150.0, $responses[1]->score->elo);
        self::assertNotNull($responses[0]->types->primary);
        self::assertSame('#FFCC33', $responses[0]->types->primary->color);
        self::assertNotNull($responses[1]->types->primary);
        self::assertSame('#F08030', $responses[1]->types->primary->color);
        self::assertNotNull($responses[1]->types->secondary);
        self::assertSame('#A890F0', $responses[1]->types->secondary->color);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = ElectionEloResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }

    #[Test]
    public function fromSqlRowCastsNullableStringFieldsFromNonStringValues(): void
    {
        $row = [
            'elo' => 1000.0,
            'significance' => false,
            'pokemon_slug' => 'eevee',
            'pokemon_name' => 'Eevee',
            'pokemon_french_name' => 'Evoli',
            'pokemon_national_dex_number' => 133,
            'pokemon_simplified_name' => 133,
            'pokemon_forms_label' => 42,
            'pokemon_simplified_french_name' => 1,
            'pokemon_forms_french_label' => 99,
            'pokemon_icon' => 7,
            'pokemon_family_order' => 1,
            'family_lead_slug' => 77,
            'original_game_bundle_slug' => 55,
            'pokemon_order_number' => '9999-0133-001',
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
            'primary_type_slug' => 'normal',
            'primary_type_name' => 'Normal',
            'primary_type_french_name' => 'Normal',
            'primary_type_color' => 42,
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame('133', $response->pokemon->labels->simplifiedName);
        self::assertSame('42', $response->pokemon->labels->formsLabel);
        self::assertSame('1', $response->pokemon->labels->simplifiedFrenchName);
        self::assertSame('99', $response->pokemon->labels->formsFrenchLabel);
        self::assertSame('7', $response->pokemon->icon);
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('77', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('55', $response->pokemon->originalGameBundle->slug);
        self::assertNotNull($response->types->primary);
        self::assertSame('42', $response->types->primary->color);
    }

    #[Test]
    public function fromSqlRowBuildsFormsWhenOnlyRegionalFormIsPresent(): void
    {
        $row = [
            'elo' => 1000.0,
            'significance' => false,
            'pokemon_slug' => 'vulpix',
            'pokemon_name' => 'Vulpix',
            'pokemon_french_name' => 'Goupix',
            'pokemon_national_dex_number' => 37,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => null,
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0037-001',
            'category_form_slug' => null,
            'category_form_name' => null,
            'category_form_french_name' => null,
            'regional_form_slug' => 'alola',
            'regional_form_name' => 'Forme d\'Alola',
            'regional_form_french_name' => 'Forme d\'Alola',
            'special_form_slug' => null,
            'special_form_name' => null,
            'special_form_french_name' => null,
            'variant_form_slug' => null,
            'variant_form_name' => null,
            'variant_form_french_name' => null,
            'primary_type_slug' => 'ice',
            'primary_type_name' => 'Ice',
            'primary_type_french_name' => 'Glace',
            'primary_type_color' => '#98D8D8',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertInstanceOf(FormsResponse::class, $response->forms);
        self::assertNull($response->forms->category);
        self::assertInstanceOf(FormResponse::class, $response->forms->regional);
        self::assertSame('alola', $response->forms->regional->slug);
        self::assertSame('Forme d\'Alola', $response->forms->regional->frenchName);
        self::assertNull($response->forms->special);
        self::assertNull($response->forms->variant);
        self::assertNull($response->pokemon->familyLead);
        self::assertNull($response->pokemon->originalGameBundle);
        self::assertNotNull($response->types->primary);
        self::assertSame('#98D8D8', $response->types->primary->color);
    }

    #[Test]
    public function fromSqlRowCastsNumericFieldsCorrectly(): void
    {
        $row = [
            'elo' => '1350.75',
            'significance' => true,
            'pokemon_slug' => 'alakazam',
            'pokemon_name' => 'Alakazam',
            'pokemon_french_name' => 'Alakazam',
            'pokemon_national_dex_number' => '65',
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'alakazam.png',
            'pokemon_family_order' => '4',
            'family_lead_slug' => 'abra',
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0065-001',
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
            'primary_type_slug' => 'psychic',
            'primary_type_name' => 'Psychic',
            'primary_type_french_name' => 'Psy',
            'primary_type_color' => '#F85888',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame(1350.75, $response->score->elo);
        self::assertSame(65, $response->pokemon->nationalDexNumber);
        self::assertSame(4, $response->pokemon->familyOrder);
        self::assertNotNull($response->types->primary);
        self::assertSame('#F85888', $response->types->primary->color);
    }

    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreNull(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles->normal);
        self::assertSame([], $response->pokemon->gameBundles->shiny);
    }

    #[Test]
    public function fromSqlRowBuildsEmptyGameBundlesWhenSlugsAreEmptyString(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => '',
            'game_bundle_shiny_slugs' => '',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles->normal);
        self::assertSame([], $response->pokemon->gameBundles->shiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesFromCommaSeparatedSlugs(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => 'redgreenblueyellow,goldsilvercrystal',
            'game_bundle_shiny_slugs' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertCount(2, $response->pokemon->gameBundles->normal);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles->normal[0]);
        self::assertSame('redgreenblueyellow', $response->pokemon->gameBundles->normal[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles->normal[1]);
        self::assertSame('goldsilvercrystal', $response->pokemon->gameBundles->normal[1]->slug);
        self::assertSame([], $response->pokemon->gameBundles->shiny);
    }

    #[Test]
    public function fromSqlRowBuildsPopulatedGameBundlesShinyFromCommaSeparatedSlugs(): void
    {
        $row = [
            'elo' => 1200.0,
            'significance' => false,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => null,
            'original_game_bundle_slug' => null,
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => 'heartgoldsoulsilver',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame([], $response->pokemon->gameBundles->normal);
        self::assertCount(1, $response->pokemon->gameBundles->shiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->gameBundles->shiny[0]);
        self::assertSame('heartgoldsoulsilver', $response->pokemon->gameBundles->shiny[0]->slug);
    }

    #[Test]
    public function fromSqlRowWithCreditColumnsBuildsImageCreditResponse(): void
    {
        $row = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
            'big_shiny_credit_name' => 'PokemonDB',
            'big_shiny_credit_url' => 'https://pokemondb.net/sprites/pikachu-shiny',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->smallRegularCredit);
        self::assertNull($response->pokemon->smallShinyCredit);
        self::assertNull($response->pokemon->bigRegularCredit);
        self::assertNotNull($response->pokemon->bigShinyCredit);
        self::assertSame('PokemonDB', $response->pokemon->bigShinyCredit->name);
        self::assertSame('https://pokemondb.net/sprites/pikachu-shiny', $response->pokemon->bigShinyCredit->url);
    }

    #[Test]
    public function fromSqlRowWithOnlyCreditNameReturnsNullCredit(): void
    {
        $row = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
            'big_shiny_credit_name' => 'PokemonDB',
            'big_shiny_credit_url' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->bigShinyCredit);
    }

    #[Test]
    public function fromSqlRowWithOnlyCreditUrlReturnsNullCredit(): void
    {
        $row = [
            'elo' => 1200.5,
            'significance' => true,
            'pokemon_slug' => 'pikachu',
            'pokemon_name' => 'Pikachu',
            'pokemon_french_name' => 'Pikachu',
            'pokemon_national_dex_number' => 25,
            'pokemon_simplified_name' => null,
            'pokemon_forms_label' => null,
            'pokemon_simplified_french_name' => null,
            'pokemon_forms_french_label' => null,
            'pokemon_icon' => 'pikachu.png',
            'pokemon_family_order' => 1,
            'family_lead_slug' => 'pichu',
            'original_game_bundle_slug' => 'red-blue',
            'pokemon_order_number' => '9999-0025-001',
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
            'primary_type_slug' => 'electric',
            'primary_type_name' => 'Electric',
            'primary_type_french_name' => 'Électrique',
            'primary_type_color' => '#FFCC33',
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
            'secondary_type_color' => null,
            'game_bundle_slugs' => null,
            'game_bundle_shiny_slugs' => null,
            'big_shiny_credit_name' => null,
            'big_shiny_credit_url' => 'https://pokemondb.net/sprites/pikachu-shiny',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertNull($response->pokemon->bigShinyCredit);
    }
}
