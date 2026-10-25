<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameHardestPokemonResponse
{
    public function __construct(
        public readonly ReportPokemonResponse $pokemon,
        public readonly int $answered,
        public readonly int $correct,
    ) {}
}
