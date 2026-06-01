<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CatchStateUsageResponse
{
    public function __construct(
        #[SerializedName('nb')]
        public readonly int $count,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $color,
    ) {}
}
