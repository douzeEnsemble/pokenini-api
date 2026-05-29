<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GenerationResponse
{
    public function __construct(
        public readonly string $slug,
    ) {}
}
