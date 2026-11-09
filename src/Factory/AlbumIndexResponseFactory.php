<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumIndexResponse;
use App\DTO\Response\AlbumPokemonResponse;
use App\DTO\Response\AlbumReportResponse;
use App\DTO\Response\LocalizedAlbumDexResponse;

final class AlbumIndexResponseFactory
{
    /**
     * @param AlbumPokemonResponse[] $pokemons
     */
    public static function fromParts(
        AlbumDexResponse|LocalizedAlbumDexResponse|null $dex,
        array $pokemons,
        AlbumReportResponse $report,
        AlbumReportResponse $filteredReport,
    ): AlbumIndexResponse {
        return new AlbumIndexResponse(
            dex: $dex,
            pokemons: $pokemons,
            report: $report,
            filteredReport: $filteredReport,
        );
    }
}
