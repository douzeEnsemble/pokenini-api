<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexLinkResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $direction,
        #[SerializedName('target_dex_slug')]
        public readonly string $targetDexSlug,
        #[SerializedName('target_name')]
        public readonly string $targetName,
        #[SerializedName('target_french_name')]
        public readonly string $targetFrenchName,
    ) {}
}
