<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\WhichShinyGameAnswerResponse;
use App\DTO\WhichShinyGameAnswer;
use App\Exception\WhichShinyGameConflictException;
use App\Exception\WhichShinyGameNotFoundException;
use App\Factory\WhichShinyGameResponseFactory;
use App\Service\WhichShinyGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/which_shiny_game/{trainerExternalId}/games')]
final class WhichShinyGameAnswerController extends AbstractController
{
    public function __construct(
        private readonly WhichShinyGameService $whichShinyGameService,
    ) {}

    #[Route(path: '/{gameId}/answers', requirements: ['gameId' => Requirement::UUID], methods: ['POST'])]
    #[Serialize]
    public function answer(
        string $trainerExternalId,
        string $gameId,
        Request $request,
    ): WhichShinyGameAnswerResponse {
        try {
            $answer = new WhichShinyGameAnswer($request->toArray());

            $result = $this->whichShinyGameService->answer($trainerExternalId, $gameId, $answer);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (WhichShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (WhichShinyGameConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        return WhichShinyGameResponseFactory::fromAnswer($result);
    }
}
