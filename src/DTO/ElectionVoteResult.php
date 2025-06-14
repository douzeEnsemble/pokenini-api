<?php

declare(strict_types=1);

namespace App\DTO;

final class ElectionVoteResult
{
    /**
     * @param PokemonElo[][] $pokemonsElo
     */
    public function __construct(
        private readonly ElectionVote $electionVote,
        private readonly array $pokemonsElo,
    ) {}

    public function getElectionVote(): ElectionVote
    {
        return $this->electionVote;
    }

    /**
     * @return PokemonElo[][]
     */
    public function getPokemonsElo(): array
    {
        return $this->pokemonsElo;
    }
}
