<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class DexResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly string $slug,
        #[SerializedName('original_slug')]
        public readonly string $originalSlug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
        #[SerializedName('is_display_form')]
        public readonly bool $isDisplayForm,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        #[SerializedName('dex_total_count')]
        public readonly int $dexTotalCount,
    ) {}
}
