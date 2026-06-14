<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ActionLogResponse
{
    public function __construct(
        #[SerializedName('action_type')]
        public readonly string $actionType,
        public readonly ?ActionLogEntryResponse $current,
        public readonly ?ActionLogEntryResponse $last,
    ) {}
}
