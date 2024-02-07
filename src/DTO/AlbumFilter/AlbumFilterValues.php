<?php

declare(strict_types=1);

namespace App\DTO\AlbumFilter;

final class AlbumFilterValues
{
    /** @var AlbumFilter[] */
    public array $values = [];

    /** @param string[] */
    public function __construct(array $values)
    {
        foreach ($values as $value) {
            $this->values[] = new AlbumFilterValue($value);
        }
    }
}
