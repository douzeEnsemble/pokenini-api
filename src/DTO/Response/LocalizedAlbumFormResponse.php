<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * A form with its name in the language the client asked for.
 */
final class LocalizedAlbumFormResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
    ) {}
}
