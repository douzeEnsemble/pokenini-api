<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumTypesResponse
{
    public function __construct(
        public readonly AlbumTypeResponse|LocalizedAlbumTypeResponse|null $primary,
        public readonly AlbumTypeResponse|LocalizedAlbumTypeResponse|null $secondary,
    ) {}
}
