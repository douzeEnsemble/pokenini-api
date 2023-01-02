<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

abstract class AbstractUpdaterService implements UpdaterServiceInterface
{
    protected Report $report;

    public function getReport(): Report
    {
        return $this->report;
    }
}
