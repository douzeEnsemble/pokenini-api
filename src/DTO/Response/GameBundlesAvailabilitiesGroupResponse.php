<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GameBundlesAvailabilitiesGroupResponse
{
    /**
     * @param GameBundleAvailabilityResponse[] $normal
     * @param GameBundleAvailabilityResponse[] $shiny
     */
    public function __construct(
        public readonly array $normal,
        public readonly array $shiny,
    ) {}
}
