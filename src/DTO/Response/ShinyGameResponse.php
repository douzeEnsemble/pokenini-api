<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ShinyGameResponse
{
    /**
     * @SuppressWarnings("PHPMD.ShortVariable")
     */
    public function __construct(
        public readonly string $id,
        public readonly ShinyGameDexSummaryResponse $dex,
        public readonly string $status,
        #[SerializedName('round_count')]
        public readonly int $roundCount,
        #[SerializedName('answered_count')]
        public readonly int $answeredCount,
        #[SerializedName('correct_count')]
        public readonly int $correctCount,
        #[SerializedName('started_at')]
        public readonly string $startedAt,
        #[SerializedName('last_played_at')]
        public readonly ?string $lastPlayedAt,
        #[SerializedName('ended_at')]
        public readonly ?string $endedAt,
    ) {}
}
