<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\WhichShinyGameResponse;
use App\DTO\Response\WhichShinyGameStateResponse;
use App\DTO\WhichShinyGameCreation;
use App\Exception\WhichShinyGameNotFoundException;
use App\Factory\WhichShinyGameResponseFactory;
use App\Service\WhichShinyGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/which_shiny_game/{trainerExternalId}/games')]
final class WhichShinyGameController extends AbstractController
{
    public function __construct(
        private readonly WhichShinyGameService $whichShinyGameService,
    ) {}

    /** @return WhichShinyGameResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId): array
    {
        return WhichShinyGameResponseFactory::fromGameRows(
            $this->whichShinyGameService->getGames($trainerExternalId),
        );
    }

    #[Route(path: '', methods: ['POST'])]
    #[Serialize(code: 201)]
    public function create(
        string $trainerExternalId,
        Request $request,
    ): WhichShinyGameResponse {
        try {
            $creation = new WhichShinyGameCreation($request->toArray());

            $game = $this->whichShinyGameService->create($trainerExternalId, $creation->dexSlug);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (WhichShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return WhichShinyGameResponseFactory::fromGameRow($game);
    }

    #[Route(path: '/{gameId}', requirements: ['gameId' => Requirement::UUID], methods: ['GET'])]
    #[Serialize]
    public function show(
        string $trainerExternalId,
        string $gameId,
    ): WhichShinyGameStateResponse {
        try {
            $state = $this->whichShinyGameService->get($trainerExternalId, $gameId);
        } catch (WhichShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return WhichShinyGameResponseFactory::fromState($state);
    }
}
