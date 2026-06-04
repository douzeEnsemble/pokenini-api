<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonEloResponse
{
    public function __construct(
        #[SerializedName('pokemon_slug')]
        public readonly string $pokemonSlug,
        public readonly int $elo,
    ) {}
}
