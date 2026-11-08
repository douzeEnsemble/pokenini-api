<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CatchStateResponse;
use App\DTO\Response\LocalizedCatchStateResponse;

final class CatchStateResponseFactory
{
    /**
     * Transform a single SQL row into CatchStateResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): CatchStateResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new CatchStateResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            color: (string) $color,
        );
    }

    /**
     * Transform a single SQL row into a LocalizedCatchStateResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function localizedFromSqlRow(array $row): LocalizedCatchStateResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['localized_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new LocalizedCatchStateResponse(
            slug: (string) $slug,
            name: (string) $name,
            color: (string) $color,
        );
    }

    /**
     * Transform multiple SQL rows into LocalizedCatchStateResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return LocalizedCatchStateResponse[]
     */
    public static function localizedFromSqlRows(array $rows): array
    {
        return array_map(self::localizedFromSqlRow(...), $rows);
    }

    /**
     * Transform multiple SQL rows into CatchStateResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return CatchStateResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
