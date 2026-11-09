<?php

declare(strict_types=1);

namespace App\Factory;

/**
 * A text which a SQL row may not hold, when the query gave the names in a single language.
 */
final class OptionalText
{
    public static function fromScalar(bool|float|int|string|null $value): ?string
    {
        return null === $value ? null : (string) $value;
    }
}
