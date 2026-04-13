<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @psalm-no-seal-properties
 */
final class GamesShiniesAvailabilities
{
    /**
     * @param bool[] $gamesShiniesAvailabilities
     */
    public function __construct(private array $gamesShiniesAvailabilities) {}

    public function __get(string $bundle): ?bool
    {
        return $this->gamesShiniesAvailabilities[$bundle] ?? null;
    }

    public function __isset(string $bundle): bool
    {
        return isset($this->gamesShiniesAvailabilities[$bundle]);
    }

    /**
     * @psalm-suppress UnusedParam
     */
    public function __set(string $bundle, bool $value): void
    {
        throw new \Exception('Use constructor please');
    }

    /**
     * @return bool[]
     */
    public function all(): array
    {
        return $this->gamesShiniesAvailabilities;
    }
}
