<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionWinCountResponse
{
    public function __construct(
        public readonly int $sum,
        public readonly int $max,
    ) {}
}
