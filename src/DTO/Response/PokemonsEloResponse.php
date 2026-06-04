<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonsEloResponse
{
    /**
     * @param PokemonEloResponse[] $winners
     * @param PokemonEloResponse[] $losers
     */
    public function __construct(
        public readonly array $winners,
        public readonly array $losers,
    ) {}
}
