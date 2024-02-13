<?php

declare(strict_types=1);

namespace App\Controller;

use App\ActionStarter\CalculateDexAvailabilitiesActionStarter;
use App\ActionStarter\CalculateGameBundlesAvailabilitiesActionStarter;
use App\ActionStarter\CalculateGameBundlesShiniesAvailabilitiesActionStarter;
use App\ActionStarter\CalculatePokemonAvailabilitiesActionStarter;
use App\ActionStarter\UpdateGamesAndDexActionStarter;
use App\ActionStarter\UpdateGamesAvailabilitiesActionStarter;
use App\ActionStarter\UpdateGamesShiniesAvailabilitiesActionStarter;
use App\ActionStarter\UpdateLabelsActionStarter;
use App\ActionStarter\UpdatePokemonsActionStarter;
use App\ActionStarter\UpdateRegionalDexNumbersActionStarter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

#[Route('/istration')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route(path: '/update/labels', methods: ['POST'])]
    public function updateLabels(
        UpdateLabelsActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/update/games_and_dex', methods: ['POST'])]
    public function updateGamesAndDex(
        UpdateGamesAndDexActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/update/pokemons', methods: ['POST'])]
    public function updatePokemons(
        UpdatePokemonsActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/update/regional_dex_numbers', methods: ['POST'])]
    public function updateRegionalDexNumbers(
        UpdateRegionalDexNumbersActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/update/games_availabilities', methods: ['POST'])]
    public function updateGamesAvailabilities(
        UpdateGamesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/update/games_shinies_availabilities', methods: ['POST'])]
    public function updateGamesShiniesAvailabilities(
        UpdateGamesShiniesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/calculate/game_bundles_availabilities', methods: ['POST'])]
    public function calculateGameBundlesAvailabilities(
        CalculateGameBundlesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/calculate/game_bundles_shinies_availabilities', methods: ['POST'])]
    public function calculateGameBundlesShiniesAvailabilities(
        CalculateGameBundlesShiniesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/calculate/dex_availabilities', methods: ['POST'])]
    public function calculateDexAvailabilities(
        CalculateDexAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/calculate/pokemon_availabilities', methods: ['POST'])]
    public function calculatePokemonAvailabilities(
        CalculatePokemonAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }
}
