<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumPokemonResponse
{
    public function __construct(
        public readonly PokemonDataResponse $pokemon,
        #[SerializedName('catch_state')]
        public readonly ?AlbumCatchStateResponse $catchState,
        #[SerializedName('category_form')]
        public readonly ?AlbumFormResponse $categoryForm,
        #[SerializedName('regional_form')]
        public readonly ?AlbumFormResponse $regionalForm,
        #[SerializedName('special_form')]
        public readonly ?AlbumFormResponse $specialForm,
        #[SerializedName('variant_form')]
        public readonly ?AlbumFormResponse $variantForm,
        #[SerializedName('primary_type')]
        public readonly ?AlbumTypeResponse $primaryType,
        #[SerializedName('secondary_type')]
        public readonly ?AlbumTypeResponse $secondaryType,
    ) {}
}
