<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionPokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        public readonly ?FormsResponse $forms,
        public readonly TypesResponse $types,
    ) {}
}
