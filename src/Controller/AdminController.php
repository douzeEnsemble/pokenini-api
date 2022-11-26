<?php

declare(strict_types=1);

namespace App\Controller;

use App\Calculator\DexAvailabilityCalculator;
use App\Calculator\GameBundleAvailabilityCalculator;
use App\Entity\GameAvailability;
use App\Service\UpdaterService\DexesUpdaterService;
use App\Service\UpdaterService\GamesUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Updater\GameAvailabilityUpdater;
use App\Updater\PokemonUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/istration')]
class AdminController extends AbstractController
{
    #[Route(path: '/update/labels', methods: ['POST'])]
    public function updateLabels(LabelsUpdaterService $labelsUpdaterService): Response
    {
        $labelsUpdaterService->execute();

        return new Response();
    }

    #[Route(path: '/update/games_and_dexes', methods: ['POST'])]
    public function updateGamesAndDexes(
        GamesUpdaterService $gamesUpdaterService,
        DexesUpdaterService $dexesUpdaterService
    ): Response {
        $gamesUpdaterService->execute();
        $dexesUpdaterService->execute();

        return new Response();
    }

    #[Route(path: '/update/pokemons', methods: ['POST'])]
    public function updatePokemons(
        PokemonUpdater $pokemonUpdater
    ): Response {
        $pokemonUpdater->execute();

        return new Response();
    }

    #[Route(path: '/update/game_availability', methods: ['POST'])]
    public function updateGameAvailability(
        GameAvailabilityUpdater $gameAvailabilityUpdater,
    ): Response {
        $gameAvailabilityUpdater->execute();

        return new Response();
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
    ): Response {
        $dexAvailabilityCalculator->execute();

        return new Response();
    }
}
