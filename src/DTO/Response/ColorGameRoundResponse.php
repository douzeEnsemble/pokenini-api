<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ColorGameRoundResponse
{
    /**
     * @param ColorGameChoiceResponse[] $choices
     */
    public function __construct(
        public readonly int $position,
        public readonly ColorGamePokemonResponse $pokemon,
        public readonly array $choices,
    ) {}
}
