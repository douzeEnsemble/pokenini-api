<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugTypesResponse
{
    public function __construct(
        public readonly ?TypeDebugResponse $primary,
        public readonly ?TypeDebugResponse $secondary,
    ) {}
}
