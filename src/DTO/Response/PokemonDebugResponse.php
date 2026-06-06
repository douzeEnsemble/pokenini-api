<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonDebugResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly ?string $identifier,
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('simplified_name')]
        public readonly string $simplifiedName,
        #[SerializedName('simplified_french_name')]
        public readonly string $simplifiedFrenchName,
        #[SerializedName('forms_label')]
        public readonly string $formsLabel,
        #[SerializedName('forms_french_label')]
        public readonly string $formsFrenchLabel,
        #[SerializedName('national_dex_number')]
        public readonly int $nationalDexNumber,
        public readonly string $family,
        public readonly bool $bankable,
        public readonly ?bool $bankableish,
        #[SerializedName('icon_name')]
        public readonly string $iconName,
        #[SerializedName('family_order')]
        public readonly int $familyOrder,
        #[SerializedName('original_game_bundle')]
        public readonly GameBundleDebugResponse $originalGameBundle,
        #[SerializedName('variant_form')]
        public readonly ?FormDebugResponse $variantForm,
        #[SerializedName('regional_form')]
        public readonly ?FormDebugResponse $regionalForm,
        #[SerializedName('special_form')]
        public readonly ?FormDebugResponse $specialForm,
        #[SerializedName('category_form')]
        public readonly ?FormDebugResponse $categoryForm,
        #[SerializedName('primary_type')]
        public readonly ?TypeDebugResponse $primaryType,
        #[SerializedName('secondary_type')]
        public readonly ?TypeDebugResponse $secondaryType,
        #[SerializedName('deleted_at')]
        public readonly ?string $deletedAt,
    ) {}
}
