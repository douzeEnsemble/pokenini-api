<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ShinyGameAnsweredRoundResponse
{
    public function __construct(
        public readonly int $position,
        public readonly ShinyGamePokemonResponse $pokemon,
        #[SerializedName('is_correct')]
        public readonly bool $isCorrect,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('chosen_shiny')]
        public readonly bool $chosenShiny,
    ) {}
}
