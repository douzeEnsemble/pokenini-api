<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Parses the colours cells of the Pokémons sheet.
 *
 * A cell holds sets separated by "|", each set holds colours separated by ";".
 * The first set is the right one, the others are decoys.
 * The three cells (codes, english names, french names) describe the same colours in the same order.
 */
final class ColorSetsParser
{
    public const string NO_VALUE = '-';

    private const string SETS_SEPARATOR = '|';
    private const string COLORS_SEPARATOR = ';';
    private const int MIN_SETS_COUNT = 2;

    /**
     * @return list<array{codes: string, names: string, french_names: string}>
     *
     * @throws \InvalidArgumentException
     */
    public static function parse(string $codes, string $names, string $frenchNames): array
    {
        if (self::isEmpty($codes) && self::isEmpty($names) && self::isEmpty($frenchNames)) {
            return [];
        }

        $codesSets = self::splitSets($codes);
        $namesSets = self::splitSets($names);
        $frenchNamesSets = self::splitSets($frenchNames);

        $setsCount = count($codesSets);

        if ($setsCount < self::MIN_SETS_COUNT) {
            throw new \InvalidArgumentException("At least 2 sets are expected, {$setsCount} given");
        }

        if (count($namesSets) !== $setsCount || count($frenchNamesSets) !== $setsCount) {
            throw new \InvalidArgumentException('Codes, names and french names must have the same number of sets');
        }

        $sets = [];
        foreach ($codesSets as $index => $codesSet) {
            $sets[] = self::buildSet($codesSet, $namesSets[$index], $frenchNamesSets[$index]);
        }

        return $sets;
    }

    private static function isEmpty(string $value): bool
    {
        $value = trim($value);

        return '' === $value || self::NO_VALUE === $value;
    }

    /**
     * @return list<list<string>>
     */
    private static function splitSets(string $value): array
    {
        if (self::isEmpty($value)) {
            return [];
        }

        return array_map(
            static fn (string $set): array => array_map(
                trim(...),
                explode(self::COLORS_SEPARATOR, $set),
            ),
            explode(self::SETS_SEPARATOR, $value),
        );
    }

    /**
     * @param list<string> $codes
     * @param list<string> $names
     * @param list<string> $frenchNames
     *
     * @return array{codes: string, names: string, french_names: string}
     */
    private static function buildSet(array $codes, array $names, array $frenchNames): array
    {
        $colorsCount = count($codes);

        if (count($names) !== $colorsCount || count($frenchNames) !== $colorsCount) {
            throw new \InvalidArgumentException('Codes, names and french names sets must have the same number of colours');
        }

        if (in_array('', [...$codes, ...$names, ...$frenchNames], true)) {
            throw new \InvalidArgumentException('A colour can not be empty');
        }

        return [
            'codes' => implode(self::COLORS_SEPARATOR, $codes),
            'names' => implode(self::COLORS_SEPARATOR, $names),
            'french_names' => implode(self::COLORS_SEPARATOR, $frenchNames),
        ];
    }
}
