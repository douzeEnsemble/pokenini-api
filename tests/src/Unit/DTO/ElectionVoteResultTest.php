<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteResult::class)]
final class ElectionVoteResultTest extends TestCase
{
    #[Test]
    public function everythingOK(): void
    {
        $electionVote = new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'election_slug' => 'douze',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);

        $pokemonsElo = [
            'winners' => [
                new PokemonElo('pikachu', 15),
            ],
            'losers' => [
                new PokemonElo('pichu', 0),
                new PokemonElo('raichu', -8000),
            ],
        ];

        $object = new ElectionVoteResult(
            $electionVote,
            $pokemonsElo,
        );

        $this->assertSame($electionVote, $object->getElectionVote());
        $this->assertSame($pokemonsElo, $object->getPokemonsElo());
    }
}
