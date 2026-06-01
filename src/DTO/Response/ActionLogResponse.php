<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ActionLogResponse
{
    public function __construct(
        public readonly ?ActionLogEntryResponse $current,
        public readonly ?ActionLogEntryResponse $last,
    ) {}
}
