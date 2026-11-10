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
        private readonly string $fallbackLocale,
    ) {}

    /**
     * @param null|string $locale the language of the names, null for the legacy ones (English and French)
     */
    public function get(string $trainerExternalId, string $dexSlug, string $electionSlug, int $count, ?string $locale = null): Report
    {
        $top = $this->trainerPokemonEloRepository->getTopN(
            $trainerExternalId,
            $dexSlug,
            $electionSlug,
            $count,
            $locale ?? $this->fallbackLocale,
            $this->fallbackLocale,
        );
        $metrics = $this->trainerPokemonEloRepository->getMetrics($trainerExternalId, $dexSlug, $electionSlug);

        return new Report($top, $metrics);
    }

    /**
     * @param null|string $locale the language of the names, null for the legacy ones (English and French)
     *
     * @return array<array-key, array<string, mixed>>
     */
    public function getEligibleDex(DexQueryOptions $options, ?string $locale = null): array
    {
        return $this->dexRepository->getCanHoldElection($options, $locale, $this->fallbackLocale);
    }

    /**
     * @param string[]    $dexSlugs
     * @param null|string $locale   the language of the names, null for the legacy ones (English and French)
     *
     * @return array<string, Report>
     */
    public function getBatch(string $trainerExternalId, array $dexSlugs, string $electionSlug, int $count, ?string $locale = null): array
    {
        $reports = [];

        foreach ($dexSlugs as $dexSlug) {
            $reports[$dexSlug] = $this->get($trainerExternalId, $dexSlug, $electionSlug, $count, $locale);
        }

        return $reports;
    }
}
