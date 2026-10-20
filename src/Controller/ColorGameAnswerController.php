<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ColorGameAnswer;
use App\DTO\Response\ColorGameAnswerResponse;
use App\Exception\ColorGameConflictException;
use App\Exception\ColorGameNotFoundException;
use App\Factory\ColorGameResponseFactory;
use App\Service\ColorGameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/color_game/{trainerExternalId}/games')]
final class ColorGameAnswerController extends AbstractController
{
    public function __construct(
        private readonly ColorGameService $colorGameService,
    ) {}

    #[Route(path: '/{gameId}/answers', requirements: ['gameId' => Requirement::UUID], methods: ['POST'])]
    #[Serialize]
    public function answer(
        string $trainerExternalId,
        string $gameId,
        Request $request,
    ): ColorGameAnswerResponse {
        try {
            $answer = new ColorGameAnswer($request->toArray());

            $result = $this->colorGameService->answer($trainerExternalId, $gameId, $answer);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (ColorGameNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ColorGameConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        return ColorGameResponseFactory::fromAnswer($result);
    }
}
