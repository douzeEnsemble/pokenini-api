<?php

namespace App\DTO\Report;

class Statistic
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $frenchName,
        public int $count = 0,
    ) {
    }

    public function increment(): int
    {
        return ++$this->count;
    }
}
