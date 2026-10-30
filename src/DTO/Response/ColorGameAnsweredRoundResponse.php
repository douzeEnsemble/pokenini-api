<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ColorGameAnsweredRoundResponse
{
    /**
     * @param ColorGameChoiceResponse[] $choices
     */
    public function __construct(
        public readonly int $position,
        public readonly ColorGamePokemonResponse $pokemon,
        #[SerializedName('is_correct')]
        public readonly bool $isCorrect,
        public readonly array $choices,
        #[SerializedName('chosen_choice')]
        public readonly int $chosenChoice,
        #[SerializedName('correct_choice')]
        public readonly int $correctChoice,
    ) {}
}
