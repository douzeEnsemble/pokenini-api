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

    /**
     * @return array<string, Report>
     */
    public function getBatch(string $trainerExternalId): array
    {
        $totals = $this->dexAvailabilitiesRepository->getBatchedTotal($trainerExternalId);
        $catchStatesCounts = $this->pokedexRepository->getBatchedCatchStatesCounts($trainerExternalId);

        $detailRowsByDexSlug = [];
        foreach ($catchStatesCounts as $row) {
            $detailRowsByDexSlug[(string) $row['dex_slug']][] = $row;
        }

        $reports = [];
        foreach ($totals as $totalRow) {
            $dexSlug = (string) $totalRow['dex_slug'];
            $total = (int) $totalRow['total'];
            $totalCaught = 0;
            $totalUncaught = $total;
            $detail = [];

            foreach ($detailRowsByDexSlug[$dexSlug] ?? [] as $row) {
                $detail[] = new Statistic(
                    slug: (string) $row['slug'],
                    name: (string) $row['name'],
                    frenchName: (string) $row['french_name'],
                    color: (string) $row['color'],
                    count: (int) $row['count'],
                );

                if ('yes' === $row['slug']) {
                    $totalCaught = (int) $row['count'];
                }

                if ('no' !== $row['slug']) {
                    $totalUncaught -= (int) $row['count'];
                }
            }

            $reports[$dexSlug] = new Report($total, $totalCaught, $totalUncaught, $detail);
        }

        return $reports;
    }
}
