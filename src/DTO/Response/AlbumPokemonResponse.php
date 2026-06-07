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
        public readonly ?AlbumFormsResponse $forms,
        public readonly AlbumTypesResponse $types,
    ) {}
}
