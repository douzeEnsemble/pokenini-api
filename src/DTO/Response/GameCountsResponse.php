<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameCountsResponse
{
    public function __construct(
        public readonly int $total,
        public readonly int $played,
        #[SerializedName('in_progress')]
        public readonly int $inProgress,
        public readonly int $completed,
        public readonly int $abandoned,
        public readonly int $players,
    ) {}
}
