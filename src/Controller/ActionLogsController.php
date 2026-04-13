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
    public function __construct() {}

    #[Route(path: '', methods: ['GET'])]
    public function get(ActionLogsService $service): JsonResponse
    {
        $actionLogs = $service->getLastests();

        /**
         * @var array<int, array{
         *  type_action: string,
         *  row_number: int,
         *  created_at: string,
         *  done_at: null|string,
         *  execution_time: int,
         *  details: array<string, string>,
         *  error_trace: null|string
         * }> $improvedActionsLogs
         */
        $improvedActionsLogs = [];
        foreach ($actionLogs as $actionLog) {
            /** @var null|array<string, string> $details */
            $details = null;

            if (null !== $actionLog['details']) {
                $jsonDetails = $actionLog['details'] ?? '';

                /** @var array<string, string> $details */
                $details = json_decode($jsonDetails, true);
            }

            $executionTime = null;
            if (null !== $actionLog['execution_time']) {
                [$executionTime, $zero] = explode('.', $actionLog['execution_time']);
                unset($zero);
            }

            $improvedActionsLogs[] = array_merge(
                $actionLog,
                [
                    'details' => $details,
                    'execution_time' => $executionTime,
                ]
            );
        }

        /**
         * @var array<string, array{
         *  current?: array<int, array{
         *      type_action: string,
         *      row_number: int,
         *      created_at: string,
         *      done_at: null|string,
         *      execution_time: string,
         *      details: array<string, string>,
         *      error_trace: null|string
         * }>,
         *  last?: array<int, array{
         *      type_action: string,
         *      row_number: int,
         *      created_at: string,
         *      done_at: null|string,
         *      execution_time: string,
         *      details: array<string, string>,
         *      error_trace: null|string
         * }>
         * }> $finalActionLogs
         */
        $finalActionLogs = [];
        foreach ($improvedActionsLogs as $actionLog) {
            $typeAction = $actionLog['type_action'];
            $period = 1 == $actionLog['row_number'] ? 'current' : 'last';

            if (!array_key_exists($typeAction, $finalActionLogs)) {
                $finalActionLogs[$typeAction] = [];
            }

            $finalActionLogs[$typeAction][$period] = $actionLog;
        }

        return new JsonResponse($finalActionLogs);
    }
}
