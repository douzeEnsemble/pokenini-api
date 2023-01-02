<?php

declare(strict_types=1);

namespace App\Controller;

use App\Calculator\DexAvailabilityCalculator;
use App\Calculator\GameBundleAvailabilityCalculator;
use App\DTO\AlbumReport\Statistic;
use App\Service\UpdaterService\GameAvailabilitiesUpdaterService;
use App\Service\UpdaterService\GamesAndDexesUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

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
        GameBundleAvailabilityCalculator $gameBundleAvailabilityCalculator,
    ): Response {
        $gameBundleAvailabilityCalculator->execute();

        return new Response();
    }

    #[Route(path: '/calculate/dex_availability', methods: ['POST'])]
    public function calculateDexAvailability(
        DexAvailabilityCalculator $dexAvailabilityCalculator,
        CacheInterface $cache
    ): Response {
        $cache->clear();

        $dexAvailabilityCalculator->execute();

        return new Response();
    }

    private function update(UpdaterServiceInterface $updaterService): JsonResponse
    {
        $updaterService->execute();

        $report = $updaterService->getReport();

        $data = [];

        /** @var Statistic $statistic */
        foreach ($report->detail as $statistic) {
            $data[$statistic->slug] = $statistic->count;
        }

        return new JsonResponse($data);
    }
}
