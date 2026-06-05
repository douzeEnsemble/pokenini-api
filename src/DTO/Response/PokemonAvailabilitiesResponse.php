<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonAvailabilitiesResponse
{
    /**
     * @param bool[] $gamesAvailabilities
     * @param bool[] $gamesShiniesAvailabilities
     * @param bool[] $gameBundlesAvailabilities
     * @param bool[] $gameBundlesShiniesAvailabilities
     */
    public function __construct(
        public readonly array $gamesAvailabilities,
        public readonly array $gamesShiniesAvailabilities,
        public readonly array $gameBundlesAvailabilities,
        public readonly array $gameBundlesShiniesAvailabilities,
    ) {}
}
