<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @psalm-no-seal-properties
 */
#[\AllowDynamicProperties]
final class CollectionsAvailabilities
{
    /**
     * @param bool[] $collectionsAvailabilities
     */
    public function __construct(private array $collectionsAvailabilities) {}

    public function __get(string $collection): ?bool
    {
        return $this->collectionsAvailabilities[$collection] ?? null;
    }

    public function __isset(string $collection): bool
    {
        return isset($this->collectionsAvailabilities[$collection]);
    }

    /**
     * @psalm-suppress UnusedParam
     */
    public function __set(string $collection, bool $value): void
    {
        throw new \Exception('Use constructor please');
    }

    /**
     * @return bool[]
     */
    public function all(): array
    {
        return $this->collectionsAvailabilities;
    }
}
