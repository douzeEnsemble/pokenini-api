<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\TrainerDexLinkResponse;

final class TrainerDexLinkResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): TrainerDexLinkResponse
    {
        /** @var scalar $linkId */
        $linkId = $row['id'];

        /** @var scalar $direction */
        $direction = $row['direction'];

        /** @var scalar $targetDexSlug */
        $targetDexSlug = $row['target_dex_slug'];

        /** @var scalar $targetName */
        $targetName = $row['target_name'];

        /** @var scalar $targetFrenchName */
        $targetFrenchName = $row['target_french_name'];

        return new TrainerDexLinkResponse(
            id: (string) $linkId,
            direction: (string) $direction,
            targetDexSlug: (string) $targetDexSlug,
            targetName: (string) $targetName,
            targetFrenchName: (string) $targetFrenchName,
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return TrainerDexLinkResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(
            static fn (array $row): TrainerDexLinkResponse => self::fromSqlRow($row),
            $rows,
        );
    }
}
