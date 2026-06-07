<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexFlagsResponse
{
    public function __construct(
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_private')]
        public readonly bool $isPrivate,
        #[SerializedName('is_on_home')]
        public readonly bool $isOnHome,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('is_custom')]
        public readonly bool $isCustom,
    ) {}
}
