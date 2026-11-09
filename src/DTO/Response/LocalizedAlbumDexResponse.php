<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * A dex with its name, its description and its region in the language the client asked for.
 */
final class LocalizedAlbumDexResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly string $slug,
        #[SerializedName('original_slug')]
        public readonly string $originalSlug,
        public readonly string $name,
        public readonly DexFlagsResponse $flags,
        #[SerializedName('display_template')]
        public readonly string $displayTemplate,
        public readonly ?LocalizedAlbumRegionResponse $region,
        #[SerializedName('selection_rule')]
        public readonly string $selectionRule,
        public readonly string $description,
        public readonly string $version,
    ) {}
}
