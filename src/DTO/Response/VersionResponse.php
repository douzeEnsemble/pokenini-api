<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class VersionResponse
{
    public function __construct(
        public readonly string $version,
        #[SerializedName('updated_at')]
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}
}
