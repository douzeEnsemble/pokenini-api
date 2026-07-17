<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ImageCreditResponse
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}
