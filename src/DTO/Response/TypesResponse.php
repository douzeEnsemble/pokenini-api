<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TypesResponse
{
    public function __construct(
        public readonly ?TypeResponse $primary,
        public readonly ?TypeResponse $secondary,
    ) {}
}
