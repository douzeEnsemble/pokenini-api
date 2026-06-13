<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionMetricsCompletionResponse;
use App\DTO\Response\ElectionMetricsResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;

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

        /** @var scalar $atMaxCount */
        $atMaxCount = $data['max_view_count'];

        /** @var scalar $underMaxCount */
        $underMaxCount = $data['under_max_view_count'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $data['dex_total_count'];

        return new ElectionMetricsResponse(
            viewCount: new ElectionViewCountResponse(
                sum: (int) $viewCountSum,
                max: (int) $viewCountMax,
            ),
            winCount: new ElectionWinCountResponse(
                sum: (int) $winCountSum,
                max: (int) $winCountMax,
            ),
            completion: new ElectionMetricsCompletionResponse(
                atMaxCount: (int) $atMaxCount,
                underMaxCount: (int) $underMaxCount,
            ),
            dexTotalCount: (int) $dexTotalCount,
        );
    }
}
