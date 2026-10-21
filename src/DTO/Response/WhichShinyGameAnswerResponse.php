<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class WhichShinyGameAnswerResponse
{
    public function __construct(
        public readonly int $position,
        #[SerializedName('is_correct')]
        public readonly bool $isCorrect,
        #[SerializedName('chosen_slot')]
        public readonly int $chosenSlot,
        #[SerializedName('shiny_slot')]
        public readonly int $shinySlot,
        public readonly WhichShinyGameResponse $game,
    ) {}
}
