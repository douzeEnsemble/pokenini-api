<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumFormsResponse
{
    public function __construct(
        public readonly ?AlbumFormResponse $category,
        public readonly ?AlbumFormResponse $regional,
        public readonly ?AlbumFormResponse $special,
        public readonly ?AlbumFormResponse $variant,
    ) {}
}
