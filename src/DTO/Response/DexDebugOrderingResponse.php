<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexDebugOrderingResponse
{
    public function __construct(
        public readonly int $main,
        public readonly int $election,
        #[SerializedName('color_game')]
        public readonly int $colorGame,
        #[SerializedName('shiny_game')]
        public readonly int $shinyGame,
        #[SerializedName('which_shiny_game')]
        public readonly int $whichShinyGame,
    ) {}
}
