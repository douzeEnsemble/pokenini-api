<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ActionLogsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/action_logs')]
class ActionLogsController extends AbstractController
{
    public function __construct() {}

    #[Route(path: '', methods: ['GET'])]
    public function get(ActionLogsService $service): JsonResponse
    {
        $actionLogs = $service->getLastests();

        array_walk(
            $actionLogs,
            function (array &$actionLog): void {
                if (null !== $actionLog['details']) {
                    $actionLog['details'] = json_decode($actionLog['details'], true);
                }

                if (null !== $actionLog['execution_time'] && is_string($actionLog['execution_time'])) {
                    [$actionLog['execution_time'], $zero] = explode('.', $actionLog['execution_time']);
                    unset($zero);
                }
            }
        );

        $finalActionLogs = [];
        foreach ($actionLogs as $actionLog) {
            $typeAction = $actionLog['type_action'];
            $period = 1 == $actionLog['row_number'] ? 'current' : 'last';

            $finalActionLogs[$typeAction][$period] = $actionLog;
        }

        return new JsonResponse($finalActionLogs);
    }
}
