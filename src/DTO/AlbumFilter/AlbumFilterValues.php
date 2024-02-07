<?php

declare(strict_types=1);

namespace App\DTO\AlbumFilter;

final class AlbumFilterValues
{
    /** @var AlbumFilterValue[] */
    public array $values = [];

    /**
     * @param string[] $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $value) {
            $this->values[] = new AlbumFilterValue($value);
        }
    }

    /**
     * @return string[]
     */
    public function extract(): array
    {
        $values = [];
        foreach ($this->values as $value) {
            $values[] = $value->value;
        }

        return $values;
    }
}
