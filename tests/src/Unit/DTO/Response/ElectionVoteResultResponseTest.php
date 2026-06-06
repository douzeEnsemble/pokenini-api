<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonsEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteResultResponse::class)]
final class ElectionVoteResultResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $electionVoteData = new ElectionVoteDataResponse(
            trainerExternalId: 'trainer1',
            dexSlug: 'national',
            electionSlug: '',
            winnersSlugs: ['pikachu'],
            losersSlugs: ['magikarp'],
        );
        $pokemonsElo = new PokemonsEloResponse(
            winners: [new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'pikachu'), elo: 1016)],
            losers: [new PokemonEloResponse(pokemon: new PokemonSlugResponse(slug: 'magikarp'), elo: 984)],
        );

        $response = new ElectionVoteResultResponse(
            electionVote: $electionVoteData,
            pokemonsElo: $pokemonsElo,
        );

        self::assertSame($electionVoteData, $response->electionVote);
        self::assertSame($pokemonsElo, $response->pokemonsElo);
    }
}
