<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class WhichShinyGameStateResponse
{
    public function __construct(
        public readonly WhichShinyGameResponse $game,
        public readonly ?WhichShinyGameRoundResponse $round,
    ) {}
}
