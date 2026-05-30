<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\CatchStateResponseFactory;
use App\Service\CatchStatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/catch_states')]
final class CatchStatesController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        CatchStatesService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $catchStates = $service->getAll();

        $responses = CatchStateResponseFactory::fromSqlRows($catchStates);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
