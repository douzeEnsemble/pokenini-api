<?php

declare(strict_types=1);

namespace App\Service\Election;

use App\DTO\DexQueryOptions;
use App\DTO\ElectionReport\Report;
use App\Repository\DexRepository;
use App\Repository\TrainerPokemonEloRepository;

class ElectionReportService
{
    public function __construct(
        private readonly TrainerPokemonEloRepository $trainerPokemonEloRepository,
        private readonly DexRepository $dexRepository,
    ) {}

    public function get(string $trainerExternalId, string $dexSlug, string $electionSlug, int $count): Report
    {
        $top = $this->trainerPokemonEloRepository->getTopN($trainerExternalId, $dexSlug, $electionSlug, $count);
        $metrics = $this->trainerPokemonEloRepository->getMetrics($trainerExternalId, $dexSlug, $electionSlug);

        return new Report($top, $metrics);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getEligibleDex(DexQueryOptions $options): array
    {
        return $this->dexRepository->getCanHoldElection($options);
    }

    /**
     * @param string[] $dexSlugs
     *
     * @return array<string, Report>
     */
    public function getBatch(string $trainerExternalId, array $dexSlugs, string $electionSlug, int $count): array
    {
        $reports = [];

        foreach ($dexSlugs as $dexSlug) {
            $reports[$dexSlug] = $this->get($trainerExternalId, $dexSlug, $electionSlug, $count);
        }

        return $reports;
    }
}
