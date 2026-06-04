<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\Factory\ElectionVoteResultResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteResultResponseFactory::class)]
final class ElectionVoteResultResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromElectionVoteResultTransformsAllFields(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer42',
            'dex_slug' => 'national',
            'election_slug' => 'gen1',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['caterpie', 'metapod'],
        ]);
        $pokemonsElo = [
            'winners' => [new PokemonElo('pikachu', 1016)],
            'losers' => [
                new PokemonElo('caterpie', 984),
                new PokemonElo('metapod', 984),
            ],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('trainer42', $response->electionVote->trainerExternalId);
        self::assertSame('national', $response->electionVote->dexSlug);
        self::assertSame('gen1', $response->electionVote->electionSlug);
        self::assertSame(['pikachu'], $response->electionVote->winnersSlugs);
        self::assertSame(['caterpie', 'metapod'], $response->electionVote->losersSlugs);
        self::assertCount(1, $response->pokemonsElo->winners);
        self::assertSame('pikachu', $response->pokemonsElo->winners[0]->pokemonSlug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertCount(2, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemonSlug);
        self::assertSame(984, $response->pokemonsElo->losers[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemonSlug);
        self::assertSame(984, $response->pokemonsElo->losers[1]->elo);
    }

    #[Test]
    public function fromElectionVoteResultHandlesEmptyPokemonLists(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => '',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => [],
        ]);
        $result = new ElectionVoteResult($electionVote, ['winners' => [], 'losers' => []]);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame([], $response->pokemonsElo->winners);
        self::assertSame([], $response->pokemonsElo->losers);
        self::assertSame([], $response->electionVote->winnersSlugs);
        self::assertSame([], $response->electionVote->losersSlugs);
    }

    #[Test]
    public function fromElectionVoteResultHandlesAllWinners(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => ['caterpie', 'metapod', 'butterfree'],
            'losers_slugs' => [],
        ]);
        $pokemonsElo = [
            'winners' => [
                new PokemonElo('caterpie', 1016),
                new PokemonElo('metapod', 1016),
                new PokemonElo('butterfree', 1016),
            ],
            'losers' => [],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertCount(3, $response->pokemonsElo->winners);
        self::assertCount(0, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->winners[0]->pokemonSlug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->winners[1]->pokemonSlug);
        self::assertSame('butterfree', $response->pokemonsElo->winners[2]->pokemonSlug);
    }

    #[Test]
    public function fromElectionVoteResultHandlesAllLosers(): void
    {
        $electionVote = new ElectionVote([
            'trainer_external_id' => 'trainer1',
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => ['caterpie', 'metapod', 'butterfree'],
        ]);
        $pokemonsElo = [
            'winners' => [],
            'losers' => [
                new PokemonElo('caterpie', 984),
                new PokemonElo('metapod', 984),
                new PokemonElo('butterfree', 984),
            ],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertCount(0, $response->pokemonsElo->winners);
        self::assertCount(3, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemonSlug);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemonSlug);
        self::assertSame('butterfree', $response->pokemonsElo->losers[2]->pokemonSlug);
    }
}
