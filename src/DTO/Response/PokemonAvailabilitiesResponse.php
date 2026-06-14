<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonAvailabilitiesResponse
{
    public function __construct(
        public readonly GamesAvailabilitiesGroupResponse $games,
        #[SerializedName('game_bundles')]
        public readonly GameBundlesAvailabilitiesGroupResponse $gameBundles,
    ) {}
}
