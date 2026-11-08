<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\FormResponse;
use App\DTO\Response\LocalizedFormResponse;

final class FormResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): FormResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new FormResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function localizedFromSqlRow(array $row): LocalizedFormResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['localized_name'];

        return new LocalizedFormResponse(
            slug: (string) $slug,
            name: (string) $name,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return LocalizedFormResponse[]
     */
    public static function localizedFromSqlRows(array $rows): array
    {
        return array_map(self::localizedFromSqlRow(...), $rows);
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return FormResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
