<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @psalm-no-seal-properties
 */
#[\AllowDynamicProperties]
final class GameBundlesShiniesAvailabilities
{
    /**
     * @param bool[] $gameBundlesShiniesAvailabilities
     */
    public function __construct(private array $gameBundlesShiniesAvailabilities) {}

    public function __get(string $bundle): ?bool
    {
        return $this->gameBundlesShiniesAvailabilities[$bundle] ?? null;
    }

    public function __isset(string $bundle): bool
    {
        return isset($this->gameBundlesShiniesAvailabilities[$bundle]);
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
        return $this->gameBundlesShiniesAvailabilities;
    }
}
