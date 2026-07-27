<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ImageCreditImageResponse
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
        public readonly string $size,
        #[SerializedName('is_shiny')]
        public readonly bool $isShiny,
    ) {}
}
