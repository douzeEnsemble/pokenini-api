<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AlbumReport\Statistic;
use App\DTO\DataChangeReport\Report;
use App\Service\CalculatorService\CalculatorServiceInterface;
use App\Service\CalculatorService\DexAvailabilitiesCalculatorService;
use App\Service\CalculatorService\GameBundleAvailabilitiesCalculatorService;
use App\Service\UpdaterService\GameAvailabilitiesUpdaterService;
use App\Service\UpdaterService\GamesAndDexesUpdaterService;
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
    public function updateLabels(LabelsUpdaterService $labelsUpdaterService): JsonResponse
    {
        return $this->update($labelsUpdaterService);
    }

    #[Route(path: '/update/games_and_dexes', methods: ['POST'])]
    public function updateGamesAndDexes(
        GamesAndDexesUpdaterService $gamesAndDexesUpdaterService
    ): JsonResponse {
        return $this->update($gamesAndDexesUpdaterService);
    }

    #[Route(path: '/update/pokemons', methods: ['POST'])]
    public function updatePokemons(
        PokemonsUpdaterService $pokemonUpdaterService
    ): JsonResponse {
        return $this->update($pokemonUpdaterService);
    }

    #[Route(path: '/update/regional_dex_number', methods: ['POST'])]
    public function updateRegionalDexNumber(
        RegionalDexNumbersUpdaterService $regionalDexNumbersUpdaterService,
    ): JsonResponse {
        return $this->update($regionalDexNumbersUpdaterService);
    }

    #[Route(path: '/update/game_availability', methods: ['POST'])]
    public function updateGameAvailability(
        GameAvailabilitiesUpdaterService $gameAvailabilitiesUpdaterService,
    ): JsonResponse {
        return $this->update($gameAvailabilitiesUpdaterService);
    }

    #[Route(path: '/calculate/game_bundle_availability', methods: ['POST'])]
    public function calculateGameBundleAvailability(
        GameBundleAvailabilitiesCalculatorService $gameBundleAvailabilitiesCalculatorService,
    ): JsonResponse {
        return $this->calculate($gameBundleAvailabilitiesCalculatorService);
    }

    #[Route(path: '/calculate/dex_availability', methods: ['POST'])]
    public function calculateDexAvailability(
        DexAvailabilitiesCalculatorService $dexAvailabilitiesCalculatorService
    ): JsonResponse {
        return $this->calculate($dexAvailabilitiesCalculatorService);
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
