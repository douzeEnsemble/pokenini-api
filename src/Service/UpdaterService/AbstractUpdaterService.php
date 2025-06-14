<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

abstract class AbstractUpdaterService implements UpdaterServiceInterface
{
    protected Report $report;

    #[\Override]
    public function getReport(): Report
    {
        return $this->report;
    }
}
