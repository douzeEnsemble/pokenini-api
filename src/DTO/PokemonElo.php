<?php

declare(strict_types=1);

namespace App\DTO;

final class PokemonElo
{
    public function __construct(
        private readonly string $pokemonSlug,
        private readonly int $elo,
    ) {}

    public function getPokemonSlug(): string
    {
        return $this->pokemonSlug;
    }

    public function getElo(): int
    {
        return $this->elo;
    }
}
