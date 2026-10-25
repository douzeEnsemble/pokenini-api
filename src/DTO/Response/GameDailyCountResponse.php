<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameDailyCountResponse
{
    public function __construct(
        public readonly string $date,
        #[SerializedName('color_game')]
        public readonly int $colorGame,
        #[SerializedName('shiny_game')]
        public readonly int $shinyGame,
        #[SerializedName('which_shiny_game')]
        public readonly int $whichShinyGame,
    ) {}
}
