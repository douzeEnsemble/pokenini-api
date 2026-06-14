<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerExternalIdResponse
{
    public function __construct(
        #[SerializedName('external_id')]
        public readonly string $externalId,
    ) {}
}
