<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionMetricsResponse;

final class ElectionMetricsResponseFactory
{
    /**
     * Transform the metrics associative array into an ElectionMetricsResponse DTO.
     *
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): ElectionMetricsResponse
    {
        /** @var scalar $viewCountSum */
        $viewCountSum = $data['view_count_sum'];

        /** @var scalar $winCountSum */
        $winCountSum = $data['win_count_sum'];

        /** @var scalar $viewCountMax */
        $viewCountMax = $data['view_count_max'];

        /** @var scalar $winCountMax */
        $winCountMax = $data['win_count_max'];

        /** @var scalar $underMaxViewCount */
        $underMaxViewCount = $data['under_max_view_count'];

        /** @var scalar $maxViewCount */
        $maxViewCount = $data['max_view_count'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $data['dex_total_count'];

        return new ElectionMetricsResponse(
            viewCountSum: (int) $viewCountSum,
            winCountSum: (int) $winCountSum,
            viewCountMax: (int) $viewCountMax,
            winCountMax: (int) $winCountMax,
            underMaxViewCount: (int) $underMaxViewCount,
            maxViewCount: (int) $maxViewCount,
            dexTotalCount: (int) $dexTotalCount,
        );
    }
}
