<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionEloScoreResponse
{
    public function __construct(
        public readonly float $elo,
        public readonly bool $significance,
    ) {}
}
