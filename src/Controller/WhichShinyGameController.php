<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\WhichShinyGameResponse;
use App\DTO\Response\WhichShinyGameRoundsResponse;
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
    use LangQueryTrait;

    /**
     * @param string[] $locales the enabled locales
     */
    public function __construct(
        private readonly WhichShinyGameService $whichShinyGameService,
        private readonly array $locales,
    ) {}

    /** @return WhichShinyGameResponse[] */
    #[Route(path: '', methods: ['GET'])]
    #[Serialize]
    public function list(string $trainerExternalId, Request $request): array
    {
        return WhichShinyGameResponseFactory::fromGameRows(
            $this->whichShinyGameService->getGames($trainerExternalId, $this->getLang($request, $this->locales)),
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

            $game = $this->whichShinyGameService->create($trainerExternalId, $creation->dexSlug, $creation->roundCount, $this->getLang($request, $this->locales));
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
        Request $request,
    ): WhichShinyGameStateResponse {
        try {
            $state = $this->whichShinyGameService->get($trainerExternalId, $gameId, $this->getLang($request, $this->locales));
        } catch (WhichShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return WhichShinyGameResponseFactory::fromState($state);
    }

    #[Route(path: '/{gameId}/rounds', requirements: ['gameId' => Requirement::UUID], methods: ['GET'])]
    #[Serialize]
    public function rounds(
        string $trainerExternalId,
        string $gameId,
        Request $request,
    ): WhichShinyGameRoundsResponse {
        try {
            $rounds = $this->whichShinyGameService->getRounds($trainerExternalId, $gameId, $this->getLang($request, $this->locales));
        } catch (WhichShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return WhichShinyGameResponseFactory::fromRounds($rounds);
    }
}
