<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class RegionResponse
{
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
