<?php

declare(strict_types=1);

namespace App\DTO\DataChangeReport;

final class Statistic
{
    public function __construct(
        public string $slug,
        public int $count = 0,
    ) {}

    public function increment(): void
    {
        ++$this->count;
    }

    public function incrementBy(int $increment): void
    {
        $this->count += $increment;
    }
}
