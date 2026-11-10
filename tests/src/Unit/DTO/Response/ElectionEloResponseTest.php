<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use App\DTO\Response\AlbumTypeResponse;
use App\DTO\Response\AlbumTypesResponse;
use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\ElectionEloScoreResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonLabelsResponse;
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
        $forms = new AlbumFormsResponse(
            category: new AlbumFormResponse('mega', 'Mega', ''),
            regional: null,
            special: null,
            variant: null,
        );
        $types = $this->makeTypes();

        $response = new ElectionEloResponse(
            pokemon: $pokemon,
            forms: $forms,
            types: $types,
            score: new ElectionEloScoreResponse(elo: 1523.75, significance: true),
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame($forms, $response->forms);
        self::assertSame($types, $response->types);
        self::assertSame(1523.75, $response->score->elo);
        self::assertTrue($response->score->significance);
    }

    #[Test]
    public function constructorAcceptsNullForms(): void
    {
        $response = new ElectionEloResponse(
            pokemon: $this->makePokemon(),
            forms: null,
            types: $this->makeTypes(),
            score: new ElectionEloScoreResponse(elo: 1000.0, significance: false),
        );

        self::assertNull($response->forms);
        self::assertFalse($response->score->significance);
    }

    private function makePokemon(): PokemonDataResponse
    {
        return new PokemonDataResponse(
            slug: 'charizard',
            labels: new PokemonLabelsResponse(
                name: 'Charizard',
                frenchName: 'Dracaufeu',
                simplifiedName: null,
                simplifiedFrenchName: null,
                formsLabel: null,
                formsFrenchLabel: null,
            ),
            nationalDexNumber: 6,
            regionalDexNumber: null,
            icon: null,
            familyOrder: 1,
            familyLead: null,
            originalGameBundle: null,
            orderNumber: '0006.001',
            gameBundles: new GameBundlesGroupResponse(normal: [], shiny: []),
            smallRegularCredit: null,
            smallShinyCredit: null,
            bigRegularCredit: null,
            bigShinyCredit: null,
        );
    }

    private function makeTypes(): AlbumTypesResponse
    {
        return new AlbumTypesResponse(
            primary: new AlbumTypeResponse('fire', 'Fire', 'Feu', '#FF4422'),
            secondary: new AlbumTypeResponse('flying', 'Flying', 'Vol', '#89AADD'),
        );
    }
}
