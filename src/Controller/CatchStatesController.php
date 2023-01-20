<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CatchStatesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/catch_states')]
class CatchStatesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CatchStatesRepository $catchStatesRepository
    ): JsonResponse {
        $catchStates = $catchStatesRepository->getAll();

        // Better with serializer ?
        return new JsonResponse($catchStates);
    }
}
