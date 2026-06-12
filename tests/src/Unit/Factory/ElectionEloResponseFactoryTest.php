<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ElectionEloResponse;
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
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame(1200.5, $response->elo);
        self::assertTrue($response->significance);
        self::assertSame('pikachu', $response->pokemon->slug);
        self::assertSame('Pikachu', $response->pokemon->name);
        self::assertNull($response->forms);
        self::assertNotNull($response->types->primary);
        self::assertSame('electric', $response->types->primary->slug);
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
            'secondary_type_slug' => 'fire',
            'secondary_type_name' => 'Fire',
            'secondary_type_french_name' => 'Feu',
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
            'secondary_type_slug' => 'poison',
            'secondary_type_name' => 'Poison',
            'secondary_type_french_name' => 'Poison',
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertFalse($response->significance);
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
                'secondary_type_slug' => null,
                'secondary_type_name' => null,
                'secondary_type_french_name' => null,
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
                'secondary_type_slug' => 'flying',
                'secondary_type_name' => 'Flying',
                'secondary_type_french_name' => 'Vol',
            ],
        ];

        $responses = ElectionEloResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ElectionEloResponse::class, $responses);
        self::assertSame('pikachu', $responses[0]->pokemon->slug);
        self::assertSame('charizard', $responses[1]->pokemon->slug);
        self::assertSame(1200.0, $responses[0]->elo);
        self::assertSame(1150.0, $responses[1]->elo);
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
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame('133', $response->pokemon->simplifiedName);
        self::assertSame('42', $response->pokemon->formsLabel);
        self::assertSame('1', $response->pokemon->simplifiedFrenchName);
        self::assertSame('99', $response->pokemon->formsFrenchLabel);
        self::assertSame('7', $response->pokemon->icon);
        self::assertInstanceOf(PokemonSlugResponse::class, $response->pokemon->familyLead);
        self::assertSame('77', $response->pokemon->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->pokemon->originalGameBundle);
        self::assertSame('55', $response->pokemon->originalGameBundle->slug);
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
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
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
            'secondary_type_slug' => null,
            'secondary_type_name' => null,
            'secondary_type_french_name' => null,
        ];

        $response = ElectionEloResponseFactory::fromSqlRow($row);

        self::assertSame(1350.75, $response->elo);
        self::assertSame(65, $response->pokemon->nationalDexNumber);
        self::assertSame(4, $response->pokemon->familyOrder);
    }
}
