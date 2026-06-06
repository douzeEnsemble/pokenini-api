<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\GameBundleDebugResponse;
use App\DTO\Response\GameGenerationDebugResponse;
use App\DTO\Response\PokemonDebugResponse;
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
            family: 'bulbasaur',
            bankable: true,
            bankableish: false,
            iconName: 'venusaur-mega',
            familyOrder: 3,
            originalGameBundle: $gameBundle,
            variantForm: null,
            regionalForm: null,
            specialForm: $form,
            categoryForm: null,
            primaryType: $type,
            secondaryType: null,
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
        self::assertSame('bulbasaur', $response->family);
        self::assertTrue($response->bankable);
        self::assertFalse($response->bankableish);
        self::assertSame('venusaur-mega', $response->iconName);
        self::assertSame(3, $response->familyOrder);
        self::assertSame($gameBundle, $response->originalGameBundle);
        self::assertNull($response->variantForm);
        self::assertNull($response->regionalForm);
        self::assertSame($form, $response->specialForm);
        self::assertNull($response->categoryForm);
        self::assertSame($type, $response->primaryType);
        self::assertNull($response->secondaryType);
        self::assertSame('2024-03-01T00:00:00+00:00', $response->deletedAt);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $generation = new GameGenerationDebugResponse(null, '1', '1', null);
        $gameBundle = new GameBundleDebugResponse(null, 'redgreenblueyellow', 'RBY', 'RBY', 1, $generation, null);

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
            family: 'bulbasaur',
            bankable: true,
            bankableish: null,
            iconName: 'bulbasaur',
            familyOrder: 0,
            originalGameBundle: $gameBundle,
            variantForm: null,
            regionalForm: null,
            specialForm: null,
            categoryForm: null,
            primaryType: null,
            secondaryType: null,
            deletedAt: null,
        );

        self::assertNull($response->identifier);
        self::assertNull($response->bankableish);
        self::assertNull($response->variantForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->categoryForm);
        self::assertNull($response->primaryType);
        self::assertNull($response->secondaryType);
        self::assertNull($response->deletedAt);
    }
}
