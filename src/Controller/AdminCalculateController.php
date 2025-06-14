<?php

declare(strict_types=1);

namespace App\Controller;

use App\ActionStarter\CalculateDexAvailabilitiesActionStarter;
use App\ActionStarter\CalculateGameBundlesAvailabilitiesActionStarter;
use App\ActionStarter\CalculateGameBundlesShiniesAvailabilitiesActionStarter;
use App\ActionStarter\CalculatePokemonAvailabilitiesActionStarter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/istration/calculate')]
class AdminCalculateController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route(path: '/game_bundles_availabilities', methods: ['POST'])]
    public function calculateGameBundlesAvailabilities(
        CalculateGameBundlesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/game_bundles_shinies_availabilities', methods: ['POST'])]
    public function calculateGameBundlesShiniesAvailabilities(
        CalculateGameBundlesShiniesAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/dex_availabilities', methods: ['POST'])]
    public function calculateDexAvailabilities(
        CalculateDexAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }

    #[Route(path: '/pokemon_availabilities', methods: ['POST'])]
    public function calculatePokemonAvailabilities(
        CalculatePokemonAvailabilitiesActionStarter $actionStarter
    ): Response {
        $message = $actionStarter->start();

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }
}
