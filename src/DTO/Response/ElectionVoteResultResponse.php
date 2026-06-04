<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteResultResponse
{
    public function __construct(
        #[SerializedName('election_vote')]
        public readonly ElectionVoteDataResponse $electionVote,
        #[SerializedName('pokemons_elo')]
        public readonly PokemonsEloResponse $pokemonsElo,
    ) {}
}
