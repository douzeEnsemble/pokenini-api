<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ShinyGameAnswerResponse
{
    public function __construct(
        public readonly int $position,
        #[SerializedName('is_correct')]
        public readonly bool $isCorrect,
        #[SerializedName('chosen_shiny')]
        public readonly bool $chosenShiny,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        public readonly ShinyGameResponse $game,
    ) {}
}
