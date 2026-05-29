<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\GameBundleResponse;
use App\DTO\Response\GenerationResponse;

final class GameBundleResponseFactory
{
    /**
     * Transform a single SQL row into GameBundleResponse DTO.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): GameBundleResponse
    {
        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new GameBundleResponse(
            slug: (string) $slug,
            name: (string) $name,
            frenchName: (string) $frenchName,
            generation: self::buildGeneration($row),
        );
    }

    /**
     * Transform multiple SQL rows into GameBundleResponse DTOs.
     *
     * @param array<array-key, array<array-key, mixed>> $rows
     *
     * @return GameBundleResponse[]
     */
    public static function fromSqlRows(array $rows): array
    {
        return array_map(self::fromSqlRow(...), $rows);
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private static function buildGeneration(array $row): GenerationResponse
    {
        /** @var scalar $slug */
        $slug = $row['generation_slug'];

        return new GenerationResponse(
            slug: (string) $slug,
        );
    }
}
