<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumDexResponse
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
        public readonly DexFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        public readonly ?AlbumRegionResponse $region,
        #[SerializedName('selection_rule')]
        public readonly string $selectionRule,
        public readonly string $description,
        #[SerializedName('french_description')]
        public readonly string $frenchDescription,
        public readonly string $version,
    ) {}
}
