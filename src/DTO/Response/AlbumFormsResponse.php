<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumFormsResponse
{
    public function __construct(
        public readonly AlbumFormResponse|LocalizedAlbumFormResponse|null $category,
        public readonly AlbumFormResponse|LocalizedAlbumFormResponse|null $regional,
        public readonly AlbumFormResponse|LocalizedAlbumFormResponse|null $special,
        public readonly AlbumFormResponse|LocalizedAlbumFormResponse|null $variant,
    ) {}
}
