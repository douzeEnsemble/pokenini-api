<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class WhichShinyGameDexResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('is_released')]
        public readonly bool $isReleased,
        #[SerializedName('is_premium')]
        public readonly bool $isPremium,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        #[SerializedName('playable_count')]
        public readonly int $playableCount,
        #[SerializedName('current_game')]
        public readonly ?WhichShinyGameResponse $currentGame,
        public readonly ?DexCategoryResponse $category,
    ) {}
}
