<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugBankResponse;
use App\DTO\Response\PokemonDebugFamilyResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use App\DTO\Response\PokemonDebugResponse;
use App\DTO\Response\PokemonDebugTypesResponse;
use App\DTO\Response\TypeDebugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugResponse::class)]
final class PokemonDebugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $generation = new GameGenerationDebugResponse(null, '6', '6', null);
        $gameBundle = new GameBundleDebugResponse(null, 'xy', 'X/Y', 'X/Y', 6, $generation, null);
        $form = new FormDebugResponse(null, 'mega', 'Mega', 'Méga', 2, null);
        $type = new TypeDebugResponse(null, 'grass', 'Grass', 'Plante', 3, '#78C850', null);
        $forms = new PokemonDebugFormsResponse(category: null, regional: null, special: $form, variant: null);
        $types = new PokemonDebugTypesResponse(primary: $type, secondary: null);
        $family = new PokemonDebugFamilyResponse(slug: 'bulbasaur');
        $bank = new PokemonDebugBankResponse(bankable: true, bankableish: false);

        $response = new PokemonDebugResponse(
            identifier: '550e8400-e29b-41d4-a716-446655440000',
            slug: 'venusaur-mega',
            name: 'Mega Venusaur',
            frenchName: 'Méga-Florizarre',
            simplifiedName: 'Venusaur',
            simplifiedFrenchName: 'Florizarre',
            formsLabel: 'Mega',
            formsFrenchLabel: 'Méga',
            nationalDexNumber: 3,
            family: $family,
            bank: $bank,
            iconName: 'venusaur-mega',
            familyOrder: 3,
            originalGameBundle: $gameBundle,
            forms: $forms,
            types: $types,
            deletedAt: '2024-03-01T00:00:00+00:00',
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->identifier);
        self::assertSame('venusaur-mega', $response->slug);
        self::assertSame('Mega Venusaur', $response->name);
        self::assertSame('Méga-Florizarre', $response->frenchName);
        self::assertSame('Venusaur', $response->simplifiedName);
        self::assertSame('Florizarre', $response->simplifiedFrenchName);
        self::assertSame('Mega', $response->formsLabel);
        self::assertSame('Méga', $response->formsFrenchLabel);
        self::assertSame(3, $response->nationalDexNumber);
        self::assertSame($family, $response->family);
        self::assertSame($bank, $response->bank);
        self::assertSame('venusaur-mega', $response->iconName);
        self::assertSame(3, $response->familyOrder);
        self::assertSame($gameBundle, $response->originalGameBundle);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $generation = new GameGenerationDebugResponse(null, '1', '1', null);
        $gameBundle = new GameBundleDebugResponse(null, 'redgreenblueyellow', 'RBY', 'RBY', 1, $generation, null);
        $types = new PokemonDebugTypesResponse(primary: null, secondary: null);
        $family = new PokemonDebugFamilyResponse(slug: 'bulbasaur');
        $bank = new PokemonDebugBankResponse(bankable: true, bankableish: null);

        $response = new PokemonDebugResponse(
            identifier: null,
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            simplifiedName: 'Bulbasaur',
            simplifiedFrenchName: 'Bulbizarre',
            formsLabel: '',
            formsFrenchLabel: '',
            nationalDexNumber: 1,
            family: $family,
            bank: $bank,
            iconName: 'bulbasaur',
            familyOrder: 0,
            originalGameBundle: $gameBundle,
            forms: null,
            types: $types,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->bank->bankableish);
        self::assertNull($response->forms);
        self::assertNull($response->deletedAt);
    }
}
