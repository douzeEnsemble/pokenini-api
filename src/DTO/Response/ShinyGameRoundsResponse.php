<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * The answered rounds of a game, in play order.
 */
final class ShinyGameRoundsResponse
{
    /**
     * @param ShinyGameAnsweredRoundResponse[] $rounds
     */
    public function __construct(
        public readonly ShinyGameResponse $game,
        public readonly array $rounds,
    ) {}
}
