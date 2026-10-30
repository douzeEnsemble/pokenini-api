<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * The answered rounds of a game, in play order.
 */
final class ColorGameRoundsResponse
{
    /**
     * @param ColorGameAnsweredRoundResponse[] $rounds
     */
    public function __construct(
        public readonly ColorGameResponse $game,
        public readonly array $rounds,
    ) {}
}
