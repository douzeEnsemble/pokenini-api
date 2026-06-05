<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\PokemonAvailabilitiesResponse;

final class PokemonAvailabilitiesResponseFactory
{
    public static function fromAvailabilities(
        GamesAvailabilities $gamesAvailabilities,
        GamesShiniesAvailabilities $gamesShiniesAvailabilities,
        GameBundlesAvailabilities $gameBundlesAvailabilities,
        GameBundlesShiniesAvailabilities $gameBundlesShiniesAvailabilities,
    ): PokemonAvailabilitiesResponse {
        return new PokemonAvailabilitiesResponse(
            gamesAvailabilities: $gamesAvailabilities->all(),
            gamesShiniesAvailabilities: $gamesShiniesAvailabilities->all(),
            gameBundlesAvailabilities: $gameBundlesAvailabilities->all(),
            gameBundlesShiniesAvailabilities: $gameBundlesShiniesAvailabilities->all(),
        );
    }
}
