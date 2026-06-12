<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;
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
        $forms = new FormsResponse(
            category: new FormResponse('starter', 'Starter', 'Partant'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = new TypesResponse(
            primary: new TypeResponse('grass', 'Grass', 'Plante', ''),
            secondary: new TypeResponse('poison', 'Poison', 'Poison', ''),
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
        $types = new TypesResponse(
            primary: new TypeResponse('grass', 'Grass', 'Plante', ''),
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
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
            orderNumber: '9999-0001-000',
            gameBundles: [],
            gameBundlesShiny: [],
        );
    }
}
