<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameRoundCountsResponse
{
    public function __construct(
        public readonly int $planned,
        public readonly int $answered,
        public readonly int $correct,
    ) {}
}
