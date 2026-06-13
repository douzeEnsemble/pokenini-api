<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionMetricsCompletionResponse
{
    public function __construct(
        #[SerializedName('at_max_count')]
        public readonly int $atMaxCount,
        #[SerializedName('under_max_count')]
        public readonly int $underMaxCount,
    ) {}
}
