<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\ShinyGameAnswerResponse;
use App\DTO\ShinyGameAnswer;
use App\Exception\ShinyGameConflictException;
use App\Exception\ShinyGameNotFoundException;
use App\Factory\ShinyGameResponseFactory;
use App\Service\ShinyGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/shiny_game/{trainerExternalId}/games')]
final class ShinyGameAnswerController extends AbstractController
{
    public function __construct(
        private readonly ShinyGameService $shinyGameService,
    ) {}

    #[Route(path: '/{gameId}/answers', requirements: ['gameId' => Requirement::UUID], methods: ['POST'])]
    #[Serialize]
    public function answer(
        string $trainerExternalId,
        string $gameId,
        Request $request,
    ): ShinyGameAnswerResponse {
        try {
            $answer = new ShinyGameAnswer($request->toArray());

            $result = $this->shinyGameService->answer($trainerExternalId, $gameId, $answer);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (ShinyGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ShinyGameConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        return ShinyGameResponseFactory::fromAnswer($result);
    }
}
