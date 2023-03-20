<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MessengerAction;
use App\Message\ActionMessageInterface;
use App\Message\CalculateDexAvailabilities;
use App\Message\CalculateGameBundlesAvailabilities;
use App\Message\UpdateLabels;
use App\Message\UpdateGamesAndDex;
use App\Message\UpdateGamesAvailabilities;
use App\Message\UpdatePokemons;
use App\Message\UpdateRegionalDexNumbers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

#[Route('/istration')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/update/labels', methods: ['POST'])]
    public function updateLabels(): Response
    {
        return $this->execute(UpdateLabels::class);
    }

    #[Route(path: '/update/games_and_dex', methods: ['POST'])]
    public function updateGamesAndDex(): Response
    {
        return $this->execute(UpdateGamesAndDex::class);
    }

    #[Route(path: '/update/pokemons', methods: ['POST'])]
    public function updatePokemons(): Response
    {
        return $this->execute(UpdatePokemons::class);
    }

    #[Route(path: '/update/regional_dex_numbers', methods: ['POST'])]
    public function updateRegionalDexNumbers(): Response
    {
        return $this->execute(UpdateRegionalDexNumbers::class);
    }

    #[Route(path: '/update/games_availabilities', methods: ['POST'])]
    public function updateGamesAvailabilities(): Response
    {
        return $this->execute(UpdateGamesAvailabilities::class);
    }

    #[Route(path: '/calculate/game_bundles_availabilities', methods: ['POST'])]
    public function calculateGameBundlesAvailabilities(): Response
    {
        return $this->execute(CalculateGameBundlesAvailabilities::class);
    }

    #[Route(path: '/calculate/dex_availabilities', methods: ['POST'])]
    public function calculateDexAvailabilities(): Response
    {
        return $this->execute(CalculateDexAvailabilities::class);
    }

    private function execute(string $messageClass): Response
    {
        $messengerAction = new MessengerAction($messageClass);

        $this->entityManager->persist($messengerAction);
        $this->entityManager->flush();

        /** @var ActionMessageInterface message */
        $message = new $messageClass(
            (string) $messengerAction->getIdentifier()
        );

        $this->bus->dispatch($message);

        return new Response('', Response::HTTP_CREATED);
    }
}
