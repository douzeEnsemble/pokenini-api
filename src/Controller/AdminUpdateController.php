<?php

declare(strict_types=1);

namespace App\Controller;

use App\ActionStarter\UpdateCollectionsAvailabilitiesActionStarter;
use App\ActionStarter\UpdateGamesAvailabilitiesActionStarter;
use App\ActionStarter\UpdateGamesCollectionsAndDexActionStarter;
use App\ActionStarter\UpdateGamesShiniesAvailabilitiesActionStarter;
use App\ActionStarter\UpdateLabelsActionStarter;
use App\ActionStarter\UpdatePokemonsActionStarter;
use App\ActionStarter\UpdateRegionalDexNumbersActionStarter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/istration/update')]
class AdminUpdateController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route(path: '/labels', methods: ['POST'])]
    public function updateLabels(
        UpdateLabelsActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/games_collections_and_dex', methods: ['POST'])]
    public function updateGamesCollectionsAndDex(
        UpdateGamesCollectionsAndDexActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/pokemons', methods: ['POST'])]
    public function updatePokemons(
        UpdatePokemonsActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/regional_dex_numbers', methods: ['POST'])]
    public function updateRegionalDexNumbers(
        UpdateRegionalDexNumbersActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/games_availabilities', methods: ['POST'])]
    public function updateGamesAvailabilities(
        UpdateGamesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/games_shinies_availabilities', methods: ['POST'])]
    public function updateGamesShiniesAvailabilities(
        UpdateGamesShiniesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/collections_availabilities', methods: ['POST'])]
    public function updateCollectionsAvailabilities(
        UpdateCollectionsAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }
}
