<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionMetricsResponse
{
    public function __construct(
        #[SerializedName('view_count_sum')]
        public readonly int $viewCountSum,
        #[SerializedName('win_count_sum')]
        public readonly int $winCountSum,
        #[SerializedName('view_count_max')]
        public readonly int $viewCountMax,
        #[SerializedName('win_count_max')]
        public readonly int $winCountMax,
        #[SerializedName('under_max_view_count')]
        public readonly int $underMaxViewCount,
        #[SerializedName('max_view_count')]
        public readonly int $maxViewCount,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
