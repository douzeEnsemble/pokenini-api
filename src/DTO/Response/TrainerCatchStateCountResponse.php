<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TrainerCatchStateCountResponse
{
    public function __construct(
        public readonly int $count,
        public readonly TrainerExternalIdResponse $trainer,
    ) {}
}
