<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class DexAvailabilitiesResponse
{
    /**
     * @param string[] $pokemons
     */
    public function __construct(
        public readonly array $pokemons,
    ) {}
}
