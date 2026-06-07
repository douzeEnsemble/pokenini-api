<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionMetricsResponse
{
    public function __construct(
        #[SerializedName('view_count')]
        public readonly ElectionViewCountResponse $viewCount,
        #[SerializedName('win_count')]
        public readonly ElectionWinCountResponse $winCount,
        #[SerializedName('under_max_view_count')]
        public readonly int $underMaxViewCount,
        #[SerializedName('max_view_count')]
        public readonly int $maxViewCount,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
