<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\PokemonDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonResponse::class)]
final class ElectionPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $pokemon = $this->buildPokemon();
        $categoryForm = new FormResponse('starter', 'Starter', 'Partant');
        $regionalForm = new FormResponse('alolan', 'Alolan', 'Alolan FR');
        $primaryType = new AlbumTypeResponse('grass', 'Grass', 'Plante');
        $secondaryType = new AlbumTypeResponse('poison', 'Poison', 'Poison');

        $response = new ElectionPokemonResponse(
            pokemon: $pokemon,
            categoryForm: $categoryForm,
            regionalForm: $regionalForm,
            specialForm: null,
            variantForm: null,
            primaryType: $primaryType,
            secondaryType: $secondaryType,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($categoryForm, $response->categoryForm);
        self::assertSame($regionalForm, $response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
        self::assertSame($primaryType, $response->primaryType);
        self::assertSame($secondaryType, $response->secondaryType);
    }

    #[Test]
    public function constructorAcceptsAllNullablePropertiesAsNull(): void
    {
        $pokemon = $this->buildPokemon();

        $response = new ElectionPokemonResponse(
            pokemon: $pokemon,
            categoryForm: null,
            regionalForm: null,
            specialForm: null,
            variantForm: null,
            primaryType: null,
            secondaryType: null,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertNull($response->categoryForm);
        self::assertNull($response->regionalForm);
        self::assertNull($response->specialForm);
        self::assertNull($response->variantForm);
        self::assertNull($response->primaryType);
        self::assertNull($response->secondaryType);
    }

    private function buildPokemon(): PokemonDataResponse
    {
        return new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '9999-0001-000',
            gameBundles: [],
            gameBundlesShiny: [],
        );
    }
}
