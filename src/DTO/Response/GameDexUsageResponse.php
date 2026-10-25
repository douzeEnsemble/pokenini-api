<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameDexUsageResponse
{
    public function __construct(
        public readonly ReportDexResponse $dex,
        public readonly int $games,
        public readonly int $players,
        public readonly int $answered,
        public readonly int $correct,
    ) {}
}
