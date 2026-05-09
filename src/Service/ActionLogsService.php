<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ActionLogsRepository;

class ActionLogsService
{
    public function __construct(
        private readonly ActionLogsRepository $repository,
    ) {}

    /**
     * @return array<int, array{
     *  type_action: string,
     *  row_number: int,
     *  created_at: string,
     *  done_at: null|string,
     *  execution_time: null|string,
     *  details: null|string,
     *  error_trace: null|string
     * }>
     */
    public function getLastests(): array
    {
        return $this->repository->getLastests();
    }

    /**
     * @return array<string, array{
     *  current?: array{
     *      type_action: string,
     *      row_number: int,
     *      created_at: string,
     *      done_at: null|string,
     *      execution_time: null|string,
     *      details: null|array<string, string>,
     *      error_trace: null|string
     *  },
     *  last?: array{
     *      type_action: string,
     *      row_number: int,
     *      created_at: string,
     *      done_at: null|string,
     *      execution_time: null|string,
     *      details: null|array<string, string>,
     *      error_trace: null|string
     *  }
     * }>
     */
    public function getFormattedLastests(): array
    {
        $result = [];
        foreach ($this->getLastests() as $actionLog) {
            $details = null;
            if (null !== $actionLog['details']) {
                /** @var array<string, string> $details */
                $details = json_decode($actionLog['details'], true);
            }

            $executionTime = null;
            if (null !== $actionLog['execution_time']) {
                [$executionTime] = explode('.', $actionLog['execution_time']);
            }

            $typeAction = $actionLog['type_action'];
            $period = 1 == $actionLog['row_number'] ? 'current' : 'last';

            if (!array_key_exists($typeAction, $result)) {
                $result[$typeAction] = [];
            }

            $result[$typeAction][$period] = array_merge($actionLog, [
                'details' => $details,
                'execution_time' => $executionTime,
            ]);
        }

        return $result;
    }
}
