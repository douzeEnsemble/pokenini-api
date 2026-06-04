<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionPokemonsListResponse
{
    /**
     * @param ElectionPokemonResponse[] $items
     */
    public function __construct(
        public readonly string $type,
        public readonly array $items,
    ) {}
}
