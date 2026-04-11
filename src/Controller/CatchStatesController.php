<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CatchStatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/catch_states')]
final class CatchStatesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CatchStatesService $service
    ): JsonResponse {
        $catchStates = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($catchStates);
    }
}
