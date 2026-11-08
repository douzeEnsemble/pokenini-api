<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * A type with its name in the language the client asked for.
 */
final class LocalizedTypeResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $color,
    ) {}
}
