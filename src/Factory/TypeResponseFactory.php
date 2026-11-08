<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\LocalizedTypeResponse;
use App\DTO\Response\TypeResponse;

final class TypeResponseFactory
{
    /**
     * Transform a single SQL row into TypeResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): TypeResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new TypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }

    /**
     * Transform a single SQL row into a LocalizedTypeResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function localizedFromSqlRow(array $row): LocalizedTypeResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['localized_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new LocalizedTypeResponse(
            slug: (string) $slug,
            name: (string) $name,
            color: (string) $color,
        );
    }

    /**
     * Transform multiple SQL rows into LocalizedTypeResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return LocalizedTypeResponse[]
     */
    public static function localizedFromSqlRows(array $rows): array
    {
        return array_map(self::localizedFromSqlRow(...), $rows);
    }

    /**
     * Transform multiple SQL rows into TypeResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return TypeResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
