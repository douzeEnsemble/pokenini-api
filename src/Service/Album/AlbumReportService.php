<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\PokedexRepository;

class AlbumReportService
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
        private readonly DexAvailabilitiesRepository $dexAvailabilitiesRepository,
    ) {
    }

    public function get(string $trainerExternalId, string $dexSlug): Report
    {
        $totalCaught = 0;
        $detail = [];

        $total = $this->dexAvailabilitiesRepository->getTotal($dexSlug);
        $totalUncaught = $total;

        $catchStatesCounts = $this->pokedexRepository->getCatchStatesCounts($trainerExternalId, $dexSlug);
        foreach ($catchStatesCounts as $catchStatesCount) {
            $detail[] = new Statistic(
                (string) $catchStatesCount['slug'],
                (string) $catchStatesCount['name'],
                (string) $catchStatesCount['french_name'],
                (int) $catchStatesCount['count'],
            );

            if ('yes' === $catchStatesCount['slug']) {
                $totalCaught = (int) $catchStatesCount['count'];
            }

            if ('no' !== $catchStatesCount['slug']) {
                $totalUncaught -= $catchStatesCount['count'];
            }
        }

        return new Report($total, $totalCaught, $totalUncaught, $detail);
    }
}
