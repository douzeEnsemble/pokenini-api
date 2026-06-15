<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumCatchStateResponse;
use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonSlugResponse;
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
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
            orderNumber: '0001-0001-000',
            gameBundles: new GameBundlesGroupResponse(
                normal: [new GameBundleSlugResponse(slug: 'redgreenblueyellow')],
                shiny: [],
            ),
        );
        $catchState = new AlbumCatchStateResponse('no', 'No', 'Non', '#e57373');
        $forms = new AlbumFormsResponse(
            category: new AlbumFormResponse('starter', 'Starter', 'de Départ'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = new AlbumTypesResponse(
            primary: new AlbumTypeResponse('grass', 'Grass', 'Plante', '#78C850'),
            secondary: new AlbumTypeResponse('poison', 'Poison', 'Poison', '#A040A0'),
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
            familyLead: new PokemonSlugResponse(slug: 'douze'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
            orderNumber: '9999-9912-000',
            gameBundles: new GameBundlesGroupResponse(
                normal: [
                    new GameBundleSlugResponse(slug: 'un'),
                    new GameBundleSlugResponse(slug: 'dos'),
                    new GameBundleSlugResponse(slug: 'tres'),
                ],
                shiny: [],
            ),
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
