<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class PokemonAvailabilitiesResponse
{
    /**
     * @param GameAvailabilityResponse[]       $gamesAvailabilities
     * @param GameAvailabilityResponse[]       $gamesShiniesAvailabilities
     * @param GameBundleAvailabilityResponse[] $gameBundlesAvailabilities
     * @param GameBundleAvailabilityResponse[] $gameBundlesShiniesAvailabilities
     */
    public function __construct(
        #[SerializedName('games_availabilities')]
        public readonly array $gamesAvailabilities,
        #[SerializedName('games_shinies_availabilities')]
        public readonly array $gamesShiniesAvailabilities,
        #[SerializedName('game_bundles_availabilities')]
        public readonly array $gameBundlesAvailabilities,
        #[SerializedName('game_bundles_shinies_availabilities')]
        public readonly array $gameBundlesShiniesAvailabilities,
    ) {}
}
