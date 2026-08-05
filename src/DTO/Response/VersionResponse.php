<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class VersionResponse
{
    public function __construct(
        public readonly string $version,
    ) {}
}
