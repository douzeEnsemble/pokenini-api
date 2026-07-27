<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImageCreditGroupResponse;
use App\DTO\Response\ImageCreditImageResponse;

final class ImageCreditGroupResponseFactory
{
    /**
     * @param array<array{
     *   source: string,
     *   images: array<array{
     *     pokemon_slug: string,
     *     pokemon_name: string,
     *     pokemon_french_name: string,
     *     pokemon_icon: string,
     *     size: string,
     *     is_shiny: bool,
     *   }>,
     * }> $groups
     *
     * @return ImageCreditGroupResponse[]
     */
    public static function fromGroupedRows(array $groups): array
    {
        return array_map(self::fromGroupedRow(...), $groups);
    }

    /**
     * @param array{
     *   source: string,
     *   images: array<array{
     *     pokemon_slug: string,
     *     pokemon_name: string,
     *     pokemon_french_name: string,
     *     pokemon_icon: string,
     *     size: string,
     *     is_shiny: bool,
     *   }>,
     * } $group
     */
    private static function fromGroupedRow(array $group): ImageCreditGroupResponse
    {
        return new ImageCreditGroupResponse(
            credit: $group['source'],
            images: array_map(self::buildImage(...), $group['images']),
        );
    }

    /**
     * @param array{pokemon_slug: string, pokemon_name: string, pokemon_french_name: string, pokemon_icon: string, size: string, is_shiny: bool} $row
     */
    private static function buildImage(array $row): ImageCreditImageResponse
    {
        return new ImageCreditImageResponse(
            pokemonSlug: $row['pokemon_slug'],
            pokemonName: $row['pokemon_name'],
            pokemonFrenchName: $row['pokemon_french_name'],
            pokemonIcon: $row['pokemon_icon'],
            size: $row['size'],
            isShiny: $row['is_shiny'],
        );
    }
}
