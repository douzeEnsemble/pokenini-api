<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ColorGameAnswerResponse
{
    public function __construct(
        public readonly int $position,
        #[SerializedName('is_correct')]
        public readonly bool $isCorrect,
        #[SerializedName('chosen_choice')]
        public readonly int $chosenChoice,
        #[SerializedName('correct_choice')]
        public readonly int $correctChoice,
        public readonly ColorGameResponse $game,
    ) {}
}
