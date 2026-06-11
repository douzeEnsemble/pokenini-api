<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameBundleAvailabilityResponse
{
    public function __construct(
        #[SerializedName('game_bundle')]
        public readonly GameBundleSlugResponse $gameBundle,
        #[SerializedName('is_available')]
        public readonly bool $isAvailable,
    ) {}
}
