<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\GameHardestPokemonResponse;
use App\DTO\Response\ReportPokemonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameHardestPokemonResponse::class)]
final class GameHardestPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $pokemon = new ReportPokemonResponse(
            slug: 'caterpie',
            name: 'Caterpie',
            frenchName: 'Chenipan',
            simplifiedName: 'Caterpie',
            simplifiedFrenchName: 'Chenipan',
            formsLabel: '',
            formsFrenchLabel: '',
            icon: 'caterpie',
        );

        $response = new GameHardestPokemonResponse(
            pokemon: $pokemon,
            answered: 5,
            correct: 1,
        );

        self::assertSame($pokemon, $response->pokemon);
        self::assertSame(5, $response->answered);
        self::assertSame(1, $response->correct);
    }
}
