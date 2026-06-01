<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\ActionLogResponseFactory;
use App\Service\ActionLogsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/action_logs')]
final class ActionLogsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(
        ActionLogsService $service,
        SerializerInterface $serializer,
    ): JsonResponse {
        $rows = $service->getLastests();
        $responses = ActionLogResponseFactory::fromSqlRows($rows);

        return JsonResponse::fromJsonString(
            $serializer->serialize($responses, 'json'),
        );
    }
}
