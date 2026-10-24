<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexCategoryResponse;

final class DexCategoryResponseFactory
{
    /**
     * Read the category columns of a dex SQL row, a dex without category gives null.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): ?DexCategoryResponse
    {
        if (null === $row['category_slug']) {
            return null;
        }

        /** @var scalar $slug */
        $slug = $row['category_slug'];

        /** @var scalar $name */
        $name = $row['category_name'];

        /** @var scalar $frenchName */
        $frenchName = $row['category_french_name'];

        /** @var scalar $orderNumber */
        $orderNumber = $row['category_order_number'];

        return new DexCategoryResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            orderNumber: (int) $orderNumber,
        );
    }
}
