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
        public readonly PokemonLabelsResponse $labels,
        #[SerializedName('national_dex_number')]
        public readonly int $nationalDexNumber,
        #[SerializedName('regional_dex_number')]
        public readonly ?int $regionalDexNumber,
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
        #[SerializedName('small_regular_credit')]
        public readonly ?ImageCreditResponse $smallRegularCredit,
        #[SerializedName('small_shiny_credit')]
        public readonly ?ImageCreditResponse $smallShinyCredit,
        #[SerializedName('big_regular_credit')]
        public readonly ?ImageCreditResponse $bigRegularCredit,
        #[SerializedName('big_shiny_credit')]
        public readonly ?ImageCreditResponse $bigShinyCredit,
    ) {}
}
