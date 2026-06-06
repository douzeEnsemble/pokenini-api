<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonsEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonsEloResponse::class)]
final class PokemonsEloResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $winner = new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'pikachu'), elo: 1016);
        $loser = new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'magikarp'), elo: 984);

        $response = new PokemonsEloResponse(
            winners: [$winner],
            losers: [$loser],
        );

        self::assertSame([$winner], $response->winners);
        self::assertSame([$loser], $response->losers);
    }

    #[Test]
    public function constructorAcceptsEmptyArrays(): void
    {
        $response = new PokemonsEloResponse(
            winners: [],
            losers: [],
        );

        self::assertSame([], $response->winners);
        self::assertSame([], $response->losers);
    }
}
