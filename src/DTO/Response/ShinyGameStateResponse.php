<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ShinyGameStateResponse
{
    public function __construct(
        public readonly ShinyGameResponse $game,
        public readonly ?ShinyGameRoundResponse $round,
    ) {}
}
