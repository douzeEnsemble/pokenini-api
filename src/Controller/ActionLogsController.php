<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ActionLogsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/action_logs')]
final class ActionLogsController extends AbstractController
{
    #[Route(path: '', methods: ['GET'])]
    public function get(ActionLogsService $service): JsonResponse
    {
        return new JsonResponse($service->getFormattedLastests());
    }
}
