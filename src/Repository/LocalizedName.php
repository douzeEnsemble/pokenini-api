<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * The SQL to get the name of a record in the requested locale.
 */
final class LocalizedName
{
    /**
     * The name in the requested locale, falling back to the fallback locale and, as a last resort,
     * to the English name column. It needs the parameters given by self::parameters().
     *
     * @param string $alias  the alias of the table, when the query has several
     * @param string $names  the JSONB column of the texts indexed by locale
     * @param string $legacy the column of the English text, the last resort
     */
    public static function sql(string $alias = '', string $names = 'names', string $legacy = 'name'): string
    {
        $prefix = '' === $alias ? '' : "{$alias}.";

        return "COALESCE(NULLIF({$prefix}{$names} ->> :locale, ''), NULLIF({$prefix}{$names} ->> :fallback_locale, ''), {$prefix}{$legacy})";
    }

    /**
     * @return array{locale: string, fallback_locale: string}
     */
    public static function parameters(string $locale, string $fallbackLocale): array
    {
        return ['locale' => $locale, 'fallback_locale' => $fallbackLocale];
    }
}
