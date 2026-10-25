<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class GameStatsResponse
{
    /**
     * @param GameDexUsageResponse[]       $dexUsage
     * @param GameHardestPokemonResponse[] $hardestPokemons
     */
    public function __construct(
        public readonly string $game,
        public readonly GameCountsResponse $games,
        public readonly GameRoundCountsResponse $rounds,
        #[SerializedName('dex_usage')]
        public readonly array $dexUsage,
        #[SerializedName('hardest_pokemons')]
        public readonly array $hardestPokemons,
    ) {}
}
