<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionPokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        #[SerializedName('category_form')]
        public readonly ?FormResponse $categoryForm,
        #[SerializedName('regional_form')]
        public readonly ?FormResponse $regionalForm,
        #[SerializedName('special_form')]
        public readonly ?FormResponse $specialForm,
        #[SerializedName('variant_form')]
        public readonly ?FormResponse $variantForm,
        #[SerializedName('primary_type')]
        public readonly ?AlbumTypeResponse $primaryType,
        #[SerializedName('secondary_type')]
        public readonly ?AlbumTypeResponse $secondaryType,
    ) {}
}
