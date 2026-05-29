<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class FormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
    ) {}
}
