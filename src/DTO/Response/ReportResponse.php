<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ReportResponse
{
    /**
     * @param TrainerCatchStateCountResponse[] $catchStateCountsDefinedByTrainer
     * @param DexUsageResponse[]               $dexUsage
     * @param CatchStateUsageResponse[]        $catchStateUsage
     */
    public function __construct(
        #[SerializedName('catch_state_counts_defined_by_trainer')]
        public readonly array $catchStateCountsDefinedByTrainer,
        #[SerializedName('dex_usage')]
        public readonly array $dexUsage,
        #[SerializedName('catch_state_usage')]
        public readonly array $catchStateUsage,
    ) {}
}
