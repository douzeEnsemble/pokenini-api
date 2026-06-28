<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ActionLogEntryResponse
{
    /**
     * @param null|array<string, string> $details
     */
    public function __construct(
        #[SerializedName('created_at')]
        public readonly string $createdAt,
        #[SerializedName('done_at')]
        public readonly ?string $doneAt,
        #[SerializedName('execution_time')]
        public readonly ?int $executionTime,
        public readonly ?array $details,
        #[SerializedName('error_trace')]
        public readonly ?string $errorTrace,
    ) {}
}
