<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class GamesAvailabilitiesGroupResponse
{
    /**
     * @param GameAvailabilityResponse[] $normal
     * @param GameAvailabilityResponse[] $shiny
     */
    public function __construct(
        public readonly array $normal,
        public readonly array $shiny,
    ) {}
}
