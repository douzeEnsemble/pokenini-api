<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ColorGameStateResponse
{
    public function __construct(
        public readonly ColorGameResponse $game,
        public readonly ?ColorGameRoundResponse $round,
    ) {}
}
