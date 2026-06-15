<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonLabelsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonLabelsResponse::class)]
final class PokemonLabelsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new PokemonLabelsResponse(
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            simplifiedName: 'bulbasaur',
            simplifiedFrenchName: 'bulbizarre',
            formsLabel: 'Mega Evolution',
            formsFrenchLabel: 'Méga-Évolution',
        );

        self::assertSame('Bulbasaur', $response->name);
        self::assertSame('Bulbizarre', $response->frenchName);
        self::assertSame('bulbasaur', $response->simplifiedName);
        self::assertSame('bulbizarre', $response->simplifiedFrenchName);
        self::assertSame('Mega Evolution', $response->formsLabel);
        self::assertSame('Méga-Évolution', $response->formsFrenchLabel);
    }

    #[Test]
    public function constructorAcceptsNullablePropertiesAsNull(): void
    {
        $response = new PokemonLabelsResponse(
            name: 'Charmander',
            frenchName: 'Salamèche',
            simplifiedName: null,
            simplifiedFrenchName: null,
            formsLabel: null,
            formsFrenchLabel: null,
        );

        self::assertSame('Charmander', $response->name);
        self::assertSame('Salamèche', $response->frenchName);
        self::assertNull($response->simplifiedName);
        self::assertNull($response->simplifiedFrenchName);
        self::assertNull($response->formsLabel);
        self::assertNull($response->formsFrenchLabel);
    }
}
