<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexDebugFlagsResponse
{
    public function __construct(
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('can_hold_election')]
        public readonly bool $canHoldElection,
    ) {}
}
