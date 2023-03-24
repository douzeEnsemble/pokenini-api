<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ActionLogsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/action_logs')]
class ActionLogsController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route(path: '', methods: ['GET'])]
    public function get(ActionLogsRepository $repo): JsonResponse
    {
        $actionLogs = $repo->getLastests();

        array_walk(
            $actionLogs,
            function (&$actionLog) {
                if (null === $actionLog['details']) {
                    return;
                }

                $actionLog['details'] = json_decode($actionLog['details'], true);
            }
        );

        return new JsonResponse($actionLogs);
    }
}
