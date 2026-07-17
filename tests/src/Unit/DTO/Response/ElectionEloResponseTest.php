<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\ElectionEloScoreResponse;
use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonLabelsResponse;
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
            category: new FormResponse('mega', 'Mega', ''),
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

    private function makeTypes(): TypesResponse
    {
        return new TypesResponse(
            primary: new TypeResponse('fire', 'Fire', 'Feu', '#FF4422'),
            secondary: new TypeResponse('flying', 'Flying', 'Vol', '#89AADD'),
        );
    }
}
