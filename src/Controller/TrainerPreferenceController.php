<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Response\TrainerPreferenceResponse;
use App\Exception\InvalidHomePageException;
use App\Service\TrainerPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/trainer_preference')]
final class TrainerPreferenceController extends AbstractController
{
    public function __construct(
        private readonly TrainerPreferenceService $service,
    ) {}

    #[Route(path: '/{trainerExternalId}', methods: ['GET'])]
    #[Serialize]
    public function get(string $trainerExternalId): TrainerPreferenceResponse
    {
        return new TrainerPreferenceResponse($this->service->getHomePage($trainerExternalId));
    }

    #[Route(path: '/{trainerExternalId}', methods: ['PUT'])]
    public function update(string $trainerExternalId, Request $request): Response
    {
        /** @var array{home_page?: mixed} $content */
        $content = json_decode($request->getContent(), true) ?? [];

        if (!isset($content['home_page']) || !\is_string($content['home_page'])) {
            throw new BadRequestHttpException();
        }

        try {
            $this->service->setHomePage($trainerExternalId, $content['home_page']);
        } catch (InvalidHomePageException $e) {
            throw new BadRequestHttpException(previous: $e);
        }

        return new Response();
    }
}
