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
}
