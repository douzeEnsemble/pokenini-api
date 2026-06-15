<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonDataResponse
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        #[SerializedName('french_name')]
        public readonly string $frenchName,
        #[SerializedName('national_dex_number')]
        public readonly int $nationalDexNumber,
        #[SerializedName('regional_dex_number')]
        public readonly ?int $regionalDexNumber,
        #[SerializedName('simplified_name')]
        public readonly ?string $simplifiedName,
        #[SerializedName('forms_label')]
        public readonly ?string $formsLabel,
        #[SerializedName('simplified_french_name')]
        public readonly ?string $simplifiedFrenchName,
        #[SerializedName('forms_french_label')]
        public readonly ?string $formsFrenchLabel,
        public readonly ?string $icon,
        #[SerializedName('family_order')]
        public readonly int $familyOrder,
        #[SerializedName('family_lead')]
        public readonly ?PokemonSlugResponse $familyLead,
        #[SerializedName('original_game_bundle')]
        public readonly ?GameBundleSlugResponse $originalGameBundle,
        #[SerializedName('order_number')]
        public readonly string $orderNumber,
        #[SerializedName('game_bundles')]
        public readonly GameBundlesGroupResponse $gameBundles,
    ) {}
}
