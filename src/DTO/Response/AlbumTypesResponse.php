<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumTypesResponse
{
    public function __construct(
        public readonly ?AlbumTypeResponse $primary,
        public readonly ?AlbumTypeResponse $secondary,
    ) {}
}
