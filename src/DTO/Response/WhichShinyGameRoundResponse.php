<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Is shiny tells which image to show: the client must not let the trainer see it.
 */
final class WhichShinyGameRoundResponse
{
    public function __construct(
        public readonly int $position,
        public readonly WhichShinyGamePokemonResponse $pokemon,
        #[SerializedName('shiny_slot')]
        public readonly int $shinySlot,
    ) {}
}
