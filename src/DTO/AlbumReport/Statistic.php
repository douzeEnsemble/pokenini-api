<?php

declare(strict_types=1);

namespace App\DTO\AlbumReport;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class Statistic
{
    public function __construct(
        #[SerializedName('slug')]
        public string $slug,
        #[SerializedName('name')]
        public string $name,
        #[SerializedName('french_name')]
        public string $frenchName,
        #[SerializedName('count')]
        public int $count = 0,
    ) {}

    public function increment(): int
    {
        return ++$this->count;
    }
}
