<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class WhichShinyGameAnsweredRoundResponse
{
    public function __construct(
        public readonly int $position,
        public readonly WhichShinyGamePokemonResponse $pokemon,
        #[SerializedName('is_correct')]
        public readonly bool $isCorrect,
        #[SerializedName('shiny_slot')]
        public readonly int $shinySlot,
        #[SerializedName('chosen_slot')]
        public readonly int $chosenSlot,
    ) {}
}
