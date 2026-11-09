<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * A region with its name in the language the client asked for.
 */
final class LocalizedAlbumRegionResponse
{
    public function __construct(
        public readonly string $name,
    ) {}
}
