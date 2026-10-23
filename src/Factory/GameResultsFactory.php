<?php

declare(strict_types=1);

namespace App\Factory;

final class GameResultsFactory
{
    /**
     * @param string $results one character per answered round, "1" when correct, "0" when wrong (str_split gives no item for an empty string)
     *
     * @return list<bool>
     */
    public static function fromString(string $results): array
    {
        return array_map(
            static fn (string $result): bool => '1' === $result,
            str_split($results),
        );
    }
}
