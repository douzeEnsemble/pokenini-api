<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\DexResponse;

final class DexResponseFactory
{
    /**
     * Transform a single SQL row into DexResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): DexResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $originalSlug */
        $originalSlug = $row['original_slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $isShiny */
        $isShiny = $row['is_shiny'];

        /** @var scalar $isDisplayForm */
        $isDisplayForm = $row['is_display_form'];

        /** @var scalar $description */
        $description = $row['description'];

        /** @var scalar $frenchDescription */
        $frenchDescription = $row['french_description'];

        /** @var scalar $isReleased */
        $isReleased = $row['is_released'];

        /** @var scalar $isPremium */
        $isPremium = $row['is_premium'];

        /** @var scalar $dexTotalCount */
        $dexTotalCount = $row['dex_total_count'];

        return new DexResponse(
            slug: (string) $slug,
            originalSlug: (string) $originalSlug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            isShiny: (bool) $isShiny,
            isDisplayForm: (bool) $isDisplayForm,
            description: (string) $description,
            frenchDescription: (string) $frenchDescription,
            isReleased: (bool) $isReleased,
            isPremium: (bool) $isPremium,
            dexTotalCount: (int) $dexTotalCount,
        );
    }

    /**
     * Transform multiple SQL rows into DexResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return DexResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }
}
