<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ElectionEloResponse;
use App\DTO\Response\ElectionEloScoreResponse;

/**
 * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
 */
final class ElectionEloResponseFactory
{
    /**
     * Transform a single SQL row into ElectionEloResponse DTO.
     *
     * @param array<string, mixed> $row
     * @param bool                 $localized the names of the forms and types in a single language, and the labels of the Pokémon by locale
     */
    public static function fromSqlRow(array $row, bool $localized = false): ElectionEloResponse
    {
        /** @var scalar $elo */
        $elo = $row['elo'];

        /** @var scalar $significance */
        $significance = $row['significance'];

        return new ElectionEloResponse(
            pokemon: AlbumPokemonResponseFactory::buildPokemon($row, $localized),
            forms: AlbumPokemonResponseFactory::buildForms($row, $localized),
            types: AlbumPokemonResponseFactory::buildTypes($row, $localized),
            score: new ElectionEloScoreResponse(
                elo: (float) $elo,
                significance: (bool) $significance,
            ),
        );
    }

    /**
     * Transform multiple SQL rows into ElectionEloResponse DTOs.
     *
     * @param array<array<string, mixed>> $rows
     *
     * @return ElectionEloResponse[]
     */
    public static function fromSqlRows(array $rows, bool $localized = false): array
    {
        return array_map(
            static fn (array $row): ElectionEloResponse => self::fromSqlRow($row, $localized),
            $rows,
        );
    }
}
