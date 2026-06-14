<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\GameBundlesAvailabilities;
use App\DTO\GameBundlesShiniesAvailabilities;
use App\DTO\GamesAvailabilities;
use App\DTO\GamesShiniesAvailabilities;
use App\DTO\Response\GameAvailabilityResponse;
use App\DTO\Response\GameBundleAvailabilityResponse;
use App\DTO\Response\GameBundlesAvailabilitiesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\GamesAvailabilitiesGroupResponse;
use App\DTO\Response\GameSlugResponse;
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
            games: new GamesAvailabilitiesGroupResponse(
                normal: self::gameAvailabilitiesFromMap($gamesAvailabilities->all()),
                shiny: self::gameAvailabilitiesFromMap($gamesShiniesAvailabilities->all()),
            ),
            gameBundles: new GameBundlesAvailabilitiesGroupResponse(
                normal: self::gameBundleAvailabilitiesFromMap($gameBundlesAvailabilities->all()),
                shiny: self::gameBundleAvailabilitiesFromMap($gameBundlesShiniesAvailabilities->all()),
            ),
        );
    }

    /**
     * @param bool[] $map
     *
     * @return GameAvailabilityResponse[]
     */
    private static function gameAvailabilitiesFromMap(array $map): array
    {
        $availabilities = [];
        foreach ($map as $slug => $isAvailable) {
            $availabilities[] = new GameAvailabilityResponse(
                game: new GameSlugResponse(slug: (string) $slug),
                isAvailable: $isAvailable,
            );
        }

        return $availabilities;
    }

    /**
     * @param bool[] $map
     *
     * @return GameBundleAvailabilityResponse[]
     */
    private static function gameBundleAvailabilitiesFromMap(array $map): array
    {
        $availabilities = [];
        foreach ($map as $slug => $isAvailable) {
            $availabilities[] = new GameBundleAvailabilityResponse(
                gameBundle: new GameBundleSlugResponse(slug: (string) $slug),
                isAvailable: $isAvailable,
            );
        }

        return $availabilities;
    }
}
