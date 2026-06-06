<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexAvailabilitiesResponse;
use App\Entity\DexAvailability;

final class DexAvailabilitiesResponseFactory
{
    /**
     * @param DexAvailability[] $dexAvailabilities
     */
    public static function fromDexAvailabilities(array $dexAvailabilities): DexAvailabilitiesResponse
    {
        $pokemons = array_map(
            static fn (DexAvailability $dexAvailability): string => $dexAvailability->pokemon->slug,
            $dexAvailabilities
        );

        return new DexAvailabilitiesResponse(pokemons: $pokemons);
    }
}
