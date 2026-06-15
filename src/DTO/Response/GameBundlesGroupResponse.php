<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameBundlesGroupResponse
{
    /**
     * @param GameBundleSlugResponse[] $normal
     * @param GameBundleSlugResponse[] $shiny
     */
    public function __construct(
        public readonly array $normal,
        public readonly array $shiny,
    ) {}
}
