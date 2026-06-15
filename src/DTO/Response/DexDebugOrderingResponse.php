<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class DexDebugOrderingResponse
{
    public function __construct(
        public readonly int $main,
        public readonly int $election,
    ) {}
}
