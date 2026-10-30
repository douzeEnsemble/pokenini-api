<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * The answered rounds of a game, in play order.
 */
final class WhichShinyGameRoundsResponse
{
    /**
     * @param WhichShinyGameAnsweredRoundResponse[] $rounds
     */
    public function __construct(
        public readonly WhichShinyGameResponse $game,
        public readonly array $rounds,
    ) {}
}
