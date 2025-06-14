<?php

declare(strict_types=1);

namespace App\DTO\AlbumFilter;

final class AlbumFilterValues
{
    /** @var AlbumFilterValue[] */
    public array $values = [];

    /** @var AlbumFilterValue[] */
    public array $negativeValues = [];

    /**
     * @param null[]|string[] $values
     */
    public function __construct(array $values)
    {
        $positivesValues = array_filter($values, fn ($value) => !is_string($value) || !str_contains($value, '!'));
        $negativesValues = array_filter($values, fn ($value) => is_string($value) && str_contains($value, '!'));

        foreach ($positivesValues as $value) {
            $this->values[] = new AlbumFilterValue($value);
        }

        foreach ($negativesValues as $value) {
            $this->negativeValues[] = new AlbumFilterValue(str_replace('!', '', $value));
        }
    }

    /**
     * @return null[]|string[]
     */
    public function extract(): array
    {
        $values = [];
        foreach ($this->values as $value) {
            $values[] = $value->value;
        }

        return $values;
    }

    /**
     * @return null[]|string[]
     */
    public function extractNegatives(): array
    {
        $values = [];
        foreach ($this->negativeValues as $value) {
            $values[] = $value->value;
        }

        return $values;
    }

    public function hasNull(): bool
    {
        $hasNull = false;
        $index = 0;
        $count = count($this->values);

        while (!$hasNull && $index < $count) {
            $hasNull = is_null($this->values[$index++]->value);
        }

        return $hasNull;
    }
}
