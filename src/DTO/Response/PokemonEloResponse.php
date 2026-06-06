<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonEloResponse
{
    public function __construct(
        public readonly PokemonSlugResponse $pokemon,
        public readonly int $elo,
    ) {}
}
