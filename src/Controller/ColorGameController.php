<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ColorGameCreation;
use App\DTO\Response\ColorGameResponse;
use App\DTO\Response\ColorGameStateResponse;
use App\Exception\ColorGameNotFoundException;
use App\Factory\ColorGameResponseFactory;
use App\Service\ColorGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/color_game/{trainerExternalId}/games')]
final class ColorGameController extends AbstractController
{
    public function __construct(
        private readonly ColorGameService $colorGameService,
    ) {}

    /** @return ColorGameResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId): array
    {
        return ColorGameResponseFactory::fromGameRows(
            $this->colorGameService->getGames($trainerExternalId),
        );
    }

    #[Route(path: '', methods: ['POST'])]
    #[Serialize(code: 201)]
    public function create(
        string $trainerExternalId,
        Request $request,
    ): ColorGameResponse {
        try {
            $creation = new ColorGameCreation($request->toArray());

            $game = $this->colorGameService->create($trainerExternalId, $creation->dexSlug, $creation->roundCount);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (ColorGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ColorGameResponseFactory::fromGameRow($game);
    }

    #[Route(path: '/{gameId}', requirements: ['gameId' => Requirement::UUID], methods: ['GET'])]
    #[Serialize]
    public function show(
        string $trainerExternalId,
        string $gameId,
    ): ColorGameStateResponse {
        try {
            $state = $this->colorGameService->get($trainerExternalId, $gameId);
        } catch (ColorGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ColorGameResponseFactory::fromState($state);
    }
}
