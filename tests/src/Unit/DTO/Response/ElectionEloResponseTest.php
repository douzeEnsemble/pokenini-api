<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\TypeResponse;
use App\DTO\Response\TypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionEloResponse::class)]
final class ElectionEloResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $pokemon = $this->makePokemon();
        $forms = new FormsResponse(
            category: new FormResponse('mega', 'Mega'),
            regional: null,
            special: null,
            variant: null,
        );
        $types = $this->makeTypes();

        $response = new ElectionEloResponse(
            pokemon: $pokemon,
            forms: $forms,
            types: $types,
            elo: 1523.75,
            significance: true,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
        self::assertSame(1523.75, $response->elo);
        self::assertTrue($response->significance);
    }

    #[Test]
    public function constructorAcceptsNullForms(): void
    {
        $response = new ElectionEloResponse(
            pokemon: $this->makePokemon(),
            forms: null,
            types: $this->makeTypes(),
            elo: 1000.0,
            significance: false,
        );

        self::assertNull($response->forms);
        self::assertFalse($response->significance);
    }

    private function makePokemon(): PokemonDataResponse
    {
        return new PokemonDataResponse(
            slug: 'charizard',
            name: 'Charizard',
            frenchName: 'Dracaufeu',
            nationalDexNumber: 6,
            simplifiedName: null,
            formsLabel: null,
            simplifiedFrenchName: null,
            formsFrenchLabel: null,
            icon: null,
            familyOrder: 1,
            familyLeadSlug: null,
            originalGameBundleSlug: null,
            orderNumber: '0006.001',
        );
    }

    private function makeTypes(): TypesResponse
    {
        return new TypesResponse(
            primary: new TypeResponse('fire', 'Fire', 'Feu', '#FF4422'),
            secondary: new TypeResponse('flying', 'Flying', 'Vol', '#89AADD'),
        );
    }
}
