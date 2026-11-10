<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionPokemonsList;
use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;

/**
 * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
 */
final class ElectionPokemonResponseFactory
{
    /**
     * @param bool $localized the names of the forms and types in a single language, and the labels of the Pokémon by locale
     */
    public static function fromElectionPokemonsList(ElectionPokemonsList $list, bool $localized = false): ElectionPokemonsListResponse
    {
        return new ElectionPokemonsListResponse(
            type: $list->getListType(),
            items: self::fromSqlRows($list->getItems(), $localized),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromSqlRow(array $row, bool $localized = false): ElectionPokemonResponse
    {
        return new ElectionPokemonResponse(
            pokemon: AlbumPokemonResponseFactory::buildPokemon($row, $localized),
            forms: AlbumPokemonResponseFactory::buildForms($row, $localized),
            types: AlbumPokemonResponseFactory::buildTypes($row, $localized),
        );
    }

    /**
     * @param array<array<string, mixed>> $rows
     *
     * @return ElectionPokemonResponse[]
     */
    public static function fromSqlRows(array $rows, bool $localized = false): array
    {
        return array_map(
            static fn (array $row): ElectionPokemonResponse => self::fromSqlRow($row, $localized),
            $rows,
        );
    }
}
