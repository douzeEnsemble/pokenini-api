<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDataResponse::class)]
final class PokemonDataResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $response = new PokemonDataResponse(
            slug: 'pikachu',
            name: 'Pikachu',
            frenchName: 'Pikachu',
            nationalDexNumber: 25,
            regionalDexNumber: 35,
            simplifiedName: 'Pikachu Base',
            formsLabel: 'Original Cap',
            simplifiedFrenchName: 'Pikachu Base FR',
            formsFrenchLabel: 'Casquette Originale',
            icon: 'pikachu.png',
            familyOrder: 1,
            familyLeadSlug: 'pichu',
            originalGameBundleSlug: 'rby',
            orderNumber: '0025.001',
            gameBundles: ['rby', 'gsc'],
            gameBundlesShiny: ['rby'],
        );

        self::assertSame('pikachu', $response->slug);
        self::assertSame('Pikachu', $response->name);
        self::assertSame('Pikachu', $response->frenchName);
        self::assertSame(25, $response->nationalDexNumber);
        self::assertSame(35, $response->regionalDexNumber);
        self::assertSame('Pikachu Base', $response->simplifiedName);
        self::assertSame('Original Cap', $response->formsLabel);
        self::assertSame('Pikachu Base FR', $response->simplifiedFrenchName);
        self::assertSame('Casquette Originale', $response->formsFrenchLabel);
        self::assertSame('pikachu.png', $response->icon);
        self::assertSame(1, $response->familyOrder);
        self::assertSame('pichu', $response->familyLeadSlug);
        self::assertSame('rby', $response->originalGameBundleSlug);
        self::assertSame('0025.001', $response->orderNumber);
        self::assertSame(['rby', 'gsc'], $response->gameBundles);
        self::assertSame(['rby'], $response->gameBundlesShiny);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: null,
            formsLabel: null,
            simplifiedFrenchName: null,
            formsFrenchLabel: null,
            icon: null,
            familyOrder: 1,
            familyLeadSlug: null,
            originalGameBundleSlug: null,
            orderNumber: '0001.001',
            gameBundles: [],
            gameBundlesShiny: [],
        );

        self::assertNull($response->regionalDexNumber);
        self::assertNull($response->simplifiedName);
        self::assertNull($response->formsLabel);
        self::assertNull($response->simplifiedFrenchName);
        self::assertNull($response->formsFrenchLabel);
        self::assertNull($response->icon);
        self::assertNull($response->familyLeadSlug);
        self::assertNull($response->originalGameBundleSlug);
        self::assertSame([], $response->gameBundles);
        self::assertSame([], $response->gameBundlesShiny);
    }
}
