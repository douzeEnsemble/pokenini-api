<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameAvailabilityResponse
{
    public function __construct(
        public readonly GameSlugResponse $game,
        #[SerializedName('is_available')]
        public readonly bool $isAvailable,
    ) {}
}
