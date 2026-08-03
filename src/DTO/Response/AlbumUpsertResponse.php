<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class AlbumUpsertResponse
{
    /**
     * @param list<string> $updatedDexSlugs
     */
    public function __construct(
        public readonly array $updatedDexSlugs,
    ) {}
}
