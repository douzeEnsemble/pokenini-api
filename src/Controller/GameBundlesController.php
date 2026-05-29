<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\GameBundleResponseFactory;
use App\Service\GameBundlesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/game_bundles')]
final class GameBundlesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        GameBundlesService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $gameBundles = $service->getAll();

        $responses = GameBundleResponseFactory::fromSqlRows($gameBundles);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
