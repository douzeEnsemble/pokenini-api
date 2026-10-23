<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ColorGameResponse
{
    /**
     * @param list<bool> $results answered rounds results, in the rounds order
     *
     * @SuppressWarnings("PHPMD.ShortVariable")
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly string $id,
        public readonly ColorGameDexSummaryResponse $dex,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        public readonly string $status,
        #[SerializedName('round_count')]
        public readonly int $roundCount,
        #[SerializedName('answered_count')]
        public readonly int $answeredCount,
        #[SerializedName('correct_count')]
        public readonly int $correctCount,
        public readonly array $results,
        #[SerializedName('started_at')]
        public readonly string $startedAt,
        #[SerializedName('last_played_at')]
        public readonly ?string $lastPlayedAt,
        #[SerializedName('ended_at')]
        public readonly ?string $endedAt,
    ) {}
}
