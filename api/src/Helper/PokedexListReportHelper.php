<?php

declare(strict_types=1);

namespace App\Helper;

use App\Entity\CatchState;

final class PokedexListReportHelper
{
    /**
     * @param string[][]|int[][] $pokedex
     * @param CatchState[] $catchStates
     *
     * @return string[][]|int[][]
     */
    public static function getReportFromPokedex(array $pokedex, array $catchStates): array
    {
        $reportDetail = [];

        foreach ($catchStates as $catchState) {
            $reportDetail[$catchState->slug] = [
                'count' => 0,
                'slug' => $catchState->slug,
                'name' => $catchState->name,
                'french_name' => $catchState->frenchName,
            ];
        }

        foreach ($pokedex as $line) {
            $catchStateSlug = $line['catch_state_slug'] ?? $catchStates[0]->slug;

            ++$reportDetail[$catchStateSlug]['count'];
        }

        return [
            'total' => count($pokedex),
            'caught' => $reportDetail['yes']['count'],
            'detail' => array_values($reportDetail),
        ];
    }
}
