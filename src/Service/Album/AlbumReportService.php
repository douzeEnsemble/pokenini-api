<?php

declare(strict_types=1);

namespace App\Service\Album;

use App\DTO\AlbumFilter\AlbumFilters;
use App\DTO\AlbumReport\Report;
use App\DTO\AlbumReport\Statistic;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\PokedexRepository;

class AlbumReportService
{
    public function __construct(
        private readonly PokedexRepository $pokedexRepository,
        private readonly DexAvailabilitiesRepository $dexAvailabilitiesRepository,
    ) {}

    public function get(string $trainerExternalId, string $dexSlug, AlbumFilters $albumFilters): Report
    {
        $totalCaught = 0;
        $detail = [];

        $total = $this->dexAvailabilitiesRepository->getTotal(
            $trainerExternalId,
            $dexSlug,
            $albumFilters,
        );
        $totalUncaught = $total;

        $catchStatesCounts = $this->pokedexRepository->getCatchStatesCounts(
            $trainerExternalId,
            $dexSlug,
            $albumFilters,
        );

        foreach ($catchStatesCounts as $catchStatesCount) {
            $detail[] = new Statistic(
                slug: (string) $catchStatesCount['slug'],
                name: (string) $catchStatesCount['name'],
                frenchName: (string) $catchStatesCount['french_name'],
                color: (string) $catchStatesCount['color'],
                count: (int) $catchStatesCount['count'],
            );

            if ('yes' === $catchStatesCount['slug']) {
                $totalCaught = (int) $catchStatesCount['count'];
            }

            if ('no' !== $catchStatesCount['slug']) {
                $totalUncaught -= (int) $catchStatesCount['count'];
            }
        }

        return new Report($total, $totalCaught, $totalUncaught, $detail);
    }
}
