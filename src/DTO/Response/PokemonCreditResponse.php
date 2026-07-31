<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonCreditResponse
{
    public function __construct(
        #[SerializedName('pokemon_slug')]
        public readonly string $pokemonSlug,
        #[SerializedName('pokemon_name')]
        public readonly string $pokemonName,
        #[SerializedName('pokemon_french_name')]
        public readonly string $pokemonFrenchName,
        #[SerializedName('pokemon_icon')]
        public readonly string $pokemonIcon,
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
