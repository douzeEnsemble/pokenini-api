<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumReport\Statistic;
use App\DTO\DataChangeReport\Report;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use App\Service\CalculatorService\GameBundlesAvailabilitiesCalculatorService;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use App\Service\UpdaterService\GamesAndDexUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/istration')]
class AdminController extends AbstractController
{
    #[Route(path: '/update/labels', methods: ['POST'])]
    public function updateLabels(LabelsUpdaterService $updaterService): JsonResponse
    {
        return $this->update($updaterService);
    }

    #[Route(path: '/update/games_and_dex', methods: ['POST'])]
    public function updateGamesAndDex(
        GamesAndDexUpdaterService $updaterService
    ): JsonResponse {
        return $this->update($updaterService);
    }

    #[Route(path: '/update/pokemons', methods: ['POST'])]
    public function updatePokemons(
        PokemonsUpdaterService $updaterService
    ): JsonResponse {
        return $this->update($updaterService);
    }

    #[Route(path: '/update/regional_dex_numbers', methods: ['POST'])]
    public function updateRegionalDexNumbers(
        RegionalDexNumbersUpdaterService $updaterService,
    ): JsonResponse {
        return $this->update($updaterService);
    }

    #[Route(path: '/update/games_availabilities', methods: ['POST'])]
    public function updateGamesAvailabilities(
        GamesAvailabilitiesUpdaterService $updaterService,
    ): JsonResponse {
        return $this->update($updaterService);
    }

    #[Route(path: '/calculate/game_bundles_availabilities', methods: ['POST'])]
    public function calculateGameBundlesAvailabilities(
        GameBundlesAvailabilitiesCalculatorService $calculatorService,
    ): JsonResponse {
        return $this->calculate($calculatorService);
    }

    #[Route(path: '/calculate/dex_availabilities', methods: ['POST'])]
    public function calculateDexAvailabilities(
        DexAvailabilitiesCalculatorService $calculatorService
    ): JsonResponse {
        return $this->calculate($calculatorService);
    }

    private function update(UpdaterServiceInterface $updaterService): JsonResponse
    {
        $updaterService->execute();

        return new JsonResponse(
            $this->reportToArray(
                $updaterService->getReport()
            )
        );
    }
    private function calculate(CalculatorServiceInterface $calculatorService): JsonResponse
    {
        $calculatorService->execute();

        return new JsonResponse(
            $this->reportToArray(
                $calculatorService->getReport()
            )
        );
    }

    private function reportToArray(Report $report): array
    {
        $data = [];

        /** @var Statistic $statistic */
        foreach ($report->detail as $statistic) {
            $data[$statistic->slug] = $statistic->count;
        }

        return $data;
    }
}
