<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CollectionResponse;

final class CollectionResponseFactory
{
    /**
     * Transform a single SQL row into CollectionResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): CollectionResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $orderNumber */
        $orderNumber = $row['order_number'];

        return new CollectionResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            orderNumber: (int) $orderNumber,
        );
    }

    /**
     * Transform multiple SQL rows into CollectionResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return CollectionResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
