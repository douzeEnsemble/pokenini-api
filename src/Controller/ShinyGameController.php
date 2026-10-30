<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ShinyGameResponse;
use App\DTO\Response\ShinyGameRoundsResponse;
use App\DTO\Response\ShinyGameStateResponse;
use App\DTO\ShinyGameCreation;
use App\Exception\ShinyGameNotFoundException;
use App\Factory\ShinyGameResponseFactory;
use App\Service\ShinyGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/shiny_game/{trainerExternalId}/games')]
final class ShinyGameController extends AbstractController
{
    public function __construct(
        private readonly ShinyGameService $shinyGameService,
    ) {}

    /** @return ShinyGameResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId): array
    {
        return ShinyGameResponseFactory::fromGameRows(
            $this->shinyGameService->getGames($trainerExternalId),
        );
    }

    #[Route(path: '', methods: ['POST'])]
    #[Serialize(code: 201)]
    public function create(
        string $trainerExternalId,
        Request $request,
    ): ShinyGameResponse {
        try {
            $creation = new ShinyGameCreation($request->toArray());

            $game = $this->shinyGameService->create($trainerExternalId, $creation->dexSlug, $creation->roundCount);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (ShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ShinyGameResponseFactory::fromGameRow($game);
    }

    #[Route(path: '/{gameId}', requirements: ['gameId' => Requirement::UUID], methods: ['GET'])]
    #[Serialize]
    public function show(
        string $trainerExternalId,
        string $gameId,
    ): ShinyGameStateResponse {
        try {
            $state = $this->shinyGameService->get($trainerExternalId, $gameId);
        } catch (ShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ShinyGameResponseFactory::fromState($state);
    }

    #[Route(path: '/{gameId}/rounds', requirements: ['gameId' => Requirement::UUID], methods: ['GET'])]
    #[Serialize]
    public function rounds(
        string $trainerExternalId,
        string $gameId,
    ): ShinyGameRoundsResponse {
        try {
            $rounds = $this->shinyGameService->getRounds($trainerExternalId, $gameId);
        } catch (ShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ShinyGameResponseFactory::fromRounds($rounds);
    }
}
