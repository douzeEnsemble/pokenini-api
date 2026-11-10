<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\ElectionPokemonResponse;
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
#[CoversClass(ElectionPokemonResponse::class)]
final class ElectionPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $pokemon = $this->buildPokemon();
        $forms = new AlbumFormsResponse(
            category: new AlbumFormResponse('starter', 'Starter', 'Partant'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = new AlbumTypesResponse(
            primary: new AlbumTypeResponse('grass', 'Grass', 'Plante', ''),
            secondary: new AlbumTypeResponse('poison', 'Poison', 'Poison', ''),
        );

        $response = new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: $forms,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
    }

    #[Test]
    public function constructorAcceptsNullForms(): void
    {
        $pokemon = $this->buildPokemon();
        $types = new AlbumTypesResponse(
            primary: new AlbumTypeResponse('grass', 'Grass', 'Plante', ''),
            secondary: null,
        );

        $response = new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: null,
            types: $types,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertNull($response->forms);
        self::assertSame($types, $response->types);
    }

    private function buildPokemon(): PokemonDataResponse
    {
        return new PokemonDataResponse(
            slug: 'bulbasaur',
            labels: new PokemonLabelsResponse(
                name: 'Bulbasaur',
                frenchName: 'Bulbizarre',
                simplifiedName: 'Bulbasaur',
                simplifiedFrenchName: 'Bulbizarre',
                formsLabel: '',
                formsFrenchLabel: '',
            ),
            nationalDexNumber: 1,
            regionalDexNumber: null,
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
            orderNumber: '9999-0001-000',
            gameBundles: new GameBundlesGroupResponse(normal: [], shiny: []),
            smallRegularCredit: null,
            smallShinyCredit: null,
            bigRegularCredit: null,
            bigShinyCredit: null,
        );
    }
}
