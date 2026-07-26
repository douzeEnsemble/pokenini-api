<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ImageCreditResponse;

final class ImageCreditResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): ImageCreditResponse
    {
        /** @var scalar $source */
        $source = $row['source'];

        return new ImageCreditResponse(credit: (string) $source);
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return ImageCreditResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
