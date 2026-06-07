<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\PokemonDataResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumPokemonResponse::class)]
final class AlbumPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $pokemon = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: 1,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLeadSlug: 'bulbasaur',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '0001-0001-000',
            gameBundles: ['redgreenblueyellow'],
            gameBundlesShiny: [],
        );
        $catchState = new AlbumCatchStateResponse('no', 'No', 'Non');
        $forms = new AlbumFormsResponse(
            category: new AlbumFormResponse('starter', 'Starter'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = new AlbumTypesResponse(
            primary: new AlbumTypeResponse('grass', 'Grass', 'Plante'),
            secondary: new AlbumTypeResponse('poison', 'Poison', 'Poison'),
        );

        $response = new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: $catchState,
            forms: $forms,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($catchState, $response->catchState);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $pokemon = new PokemonDataResponse(
            slug: 'douze',
            name: 'Douze',
            frenchName: 'Douze',
            nationalDexNumber: 9912,
            regionalDexNumber: null,
            simplifiedName: 'Douze',
            formsLabel: '',
            simplifiedFrenchName: 'Douze',
            formsFrenchLabel: '',
            icon: 'douze',
            familyOrder: 0,
            familyLeadSlug: 'douze',
            originalGameBundleSlug: 'redgreenblueyellow',
            orderNumber: '9999-9912-000',
            gameBundles: ['un', 'dos', 'tres'],
            gameBundlesShiny: [],
        );
        $types = new AlbumTypesResponse(primary: null, secondary: null);

        $response = new AlbumPokemonResponse(
            pokemon: $pokemon,
            catchState: null,
            forms: null,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertNull($response->catchState);
        self::assertNull($response->forms);
        self::assertSame($types, $response->types);
    }
}
