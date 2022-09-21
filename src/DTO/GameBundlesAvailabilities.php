<?php

declare(strict_types=1);

namespace App\DTO;

class GameBundlesAvailabilities
{
    /**
     * @param bool[] $gameBundlesAvailabilities
     */
    public function __construct(private array $gameBundlesAvailabilities)
    {
    }

    public function __get(string $bundle): ?bool
    {
        return $this->gameBundlesAvailabilities[$bundle] ?? null;
    }

    public function __isset(string $bundle): bool
    {
        return isset($this->gameBundlesAvailabilities[$bundle]);
    }

    public function __set(string $bundle, bool $value): void
    {
        throw new \Exception("Use constructor please");
    }
}
