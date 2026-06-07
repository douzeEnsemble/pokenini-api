<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerDexResponse
{
    public function __construct(
        public readonly DexSlugResponse $dex,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        public readonly string $slug,
        public readonly DexFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
    ) {}
}
