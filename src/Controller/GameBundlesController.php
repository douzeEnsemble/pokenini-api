<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\GameBundlesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game_bundles')]
final class GameBundlesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        GameBundlesService $service
    ): JsonResponse {
        $gameBundles = $service->getAll();

        // Better with serializer ?
        return new JsonResponse($gameBundles);
    }
}
