<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImageCreditResponse;
use App\DTO\Response\PokemonCreditResponse;

final class PokemonCreditResponseFactory
{
    /**
     * @param array<array{
     *   pokemon_slug: string,
     *   pokemon_name: string,
     *   pokemon_french_name: string,
     *   pokemon_icon: string,
     *   small_regular_credit: ?string,
     *   small_shiny_credit: ?string,
     *   big_regular_credit: ?string,
     *   big_shiny_credit: ?string,
     * }> $rows
     *
     * @return PokemonCreditResponse[]
     */
    public static function fromRows(array $rows): array
    {
        return array_map(self::fromRow(...), $rows);
    }

    /**
     * @param array{
     *   pokemon_slug: string,
     *   pokemon_name: string,
     *   pokemon_french_name: string,
     *   pokemon_icon: string,
     *   small_regular_credit: ?string,
     *   small_shiny_credit: ?string,
     *   big_regular_credit: ?string,
     *   big_shiny_credit: ?string,
     * } $row
     */
    private static function fromRow(array $row): PokemonCreditResponse
    {
        return new PokemonCreditResponse(
            pokemonSlug: $row['pokemon_slug'],
            pokemonName: $row['pokemon_name'],
            pokemonFrenchName: $row['pokemon_french_name'],
            pokemonIcon: $row['pokemon_icon'],
            smallRegularCredit: self::buildCredit($row['small_regular_credit']),
            smallShinyCredit: self::buildCredit($row['small_shiny_credit']),
            bigRegularCredit: self::buildCredit($row['big_regular_credit']),
            bigShinyCredit: self::buildCredit($row['big_shiny_credit']),
        );
    }

    private static function buildCredit(?string $credit): ?ImageCreditResponse
    {
        return null !== $credit ? new ImageCreditResponse($credit) : null;
    }
}
