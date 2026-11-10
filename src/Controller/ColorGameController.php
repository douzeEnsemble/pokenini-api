<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ColorGameCreation;
use App\DTO\Response\ColorGameResponse;
use App\DTO\Response\ColorGameRoundsResponse;
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
    use LangQueryTrait;

    /**
     * @param string[] $locales the enabled locales
     */
    public function __construct(
        private readonly ColorGameService $colorGameService,
        private readonly array $locales,
    ) {}

    /** @return ColorGameResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId, Request $request): array
    {
        return ColorGameResponseFactory::fromGameRows(
            $this->colorGameService->getGames($trainerExternalId, $this->getLang($request, $this->locales)),
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

            $game = $this->colorGameService->create($trainerExternalId, $creation->dexSlug, $creation->roundCount, $this->getLang($request, $this->locales));
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
        Request $request,
    ): ColorGameStateResponse {
        try {
            $state = $this->colorGameService->get($trainerExternalId, $gameId, $this->getLang($request, $this->locales));
        } catch (ColorGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ColorGameResponseFactory::fromState($state);
    }

    #[Route(path: '/{gameId}/rounds', requirements: ['gameId' => Requirement::UUID], methods: ['GET'])]
    #[Serialize]
    public function rounds(
        string $trainerExternalId,
        string $gameId,
        Request $request,
    ): ColorGameRoundsResponse {
        try {
            $rounds = $this->colorGameService->getRounds($trainerExternalId, $gameId, $this->getLang($request, $this->locales));
        } catch (ColorGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return ColorGameResponseFactory::fromRounds($rounds);
    }
}
