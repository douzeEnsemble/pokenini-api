<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * A collection with its name in the language the client asked for.
 */
final class LocalizedCollectionResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('order_number')]
        public readonly int $orderNumber,
    ) {}
}
