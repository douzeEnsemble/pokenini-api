<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonLabelsResponse;
use App\DTO\Response\PokemonSlugResponse;
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
        $normalBundles = [
            new GameBundleSlugResponse(slug: 'rby'),
            new GameBundleSlugResponse(slug: 'gsc'),
        ];
        $shinyBundles = [
            new GameBundleSlugResponse(slug: 'rby'),
        ];

        $labels = new PokemonLabelsResponse(
            name: 'Pikachu',
            frenchName: 'Pikachu',
            simplifiedName: 'Pikachu Base',
            simplifiedFrenchName: 'Pikachu Base FR',
            formsLabel: 'Original Cap',
            formsFrenchLabel: 'Casquette Originale',
        );

        $response = new PokemonDataResponse(
            slug: 'pikachu',
            labels: $labels,
            nationalDexNumber: 25,
            regionalDexNumber: 35,
            icon: 'pikachu.png',
            familyOrder: 1,
            familyLead: new PokemonSlugResponse(slug: 'pichu'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'rby'),
            orderNumber: '0025.001',
            gameBundles: new GameBundlesGroupResponse(normal: $normalBundles, shiny: $shinyBundles),
            smallRegularCredit: null,
            smallShinyCredit: null,
            bigRegularCredit: null,
            bigShinyCredit: null,
        );

        self::assertSame('pikachu', $response->slug);
        self::assertSame('Pikachu', $response->labels->name);
        self::assertSame('Pikachu', $response->labels->frenchName);
        self::assertSame('Pikachu Base', $response->labels->simplifiedName);
        self::assertSame('Pikachu Base FR', $response->labels->simplifiedFrenchName);
        self::assertSame('Original Cap', $response->labels->formsLabel);
        self::assertSame('Casquette Originale', $response->labels->formsFrenchLabel);
        self::assertSame(25, $response->nationalDexNumber);
        self::assertSame(35, $response->regionalDexNumber);
        self::assertSame('pikachu.png', $response->icon);
        self::assertSame(1, $response->familyOrder);
        self::assertInstanceOf(PokemonSlugResponse::class, $response->familyLead);
        self::assertSame('pichu', $response->familyLead->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->originalGameBundle);
        self::assertSame('rby', $response->originalGameBundle->slug);
        self::assertSame('0025.001', $response->orderNumber);
        self::assertCount(2, $response->gameBundles->normal);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->gameBundles->normal[0]);
        self::assertSame('rby', $response->gameBundles->normal[0]->slug);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->gameBundles->normal[1]);
        self::assertSame('gsc', $response->gameBundles->normal[1]->slug);
        self::assertCount(1, $response->gameBundles->shiny);
        self::assertInstanceOf(GameBundleSlugResponse::class, $response->gameBundles->shiny[0]);
        self::assertSame('rby', $response->gameBundles->shiny[0]->slug);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $labels = new PokemonLabelsResponse(
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            simplifiedName: null,
            simplifiedFrenchName: null,
            formsLabel: null,
            formsFrenchLabel: null,
        );

        $response = new PokemonDataResponse(
            slug: 'bulbasaur',
            labels: $labels,
            nationalDexNumber: 1,
            regionalDexNumber: null,
            icon: null,
            familyOrder: 1,
            familyLead: null,
            originalGameBundle: null,
            orderNumber: '0001.001',
            gameBundles: new GameBundlesGroupResponse(normal: [], shiny: []),
            smallRegularCredit: null,
            smallShinyCredit: null,
            bigRegularCredit: null,
            bigShinyCredit: null,
        );

        self::assertNull($response->regionalDexNumber);
        self::assertNull($response->labels->simplifiedName);
        self::assertNull($response->labels->simplifiedFrenchName);
        self::assertNull($response->labels->formsLabel);
        self::assertNull($response->labels->formsFrenchLabel);
        self::assertNull($response->icon);
        self::assertNull($response->familyLead);
        self::assertNull($response->originalGameBundle);
        self::assertSame([], $response->gameBundles->normal);
        self::assertSame([], $response->gameBundles->shiny);
    }
}
