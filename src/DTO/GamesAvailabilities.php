<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @psalm-no-seal-properties
 */
final class GamesAvailabilities
{
    /**
     * @param bool[] $gamesAvailabilities
     */
    public function __construct(private array $gamesAvailabilities) {}

    public function __get(string $game): ?bool
    {
        return $this->gamesAvailabilities[$game] ?? null;
    }

    public function __isset(string $game): bool
    {
        return isset($this->gamesAvailabilities[$game]);
    }

    public function __set(string $game, bool $value): void
    {
        throw new \Exception('Use constructor please');
    }

    /**
     * @return bool[]
     */
    public function all(): array
    {
        return $this->gamesAvailabilities;
    }
}
