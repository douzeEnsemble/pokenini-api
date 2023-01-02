<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

interface UpdaterServiceInterface
{
    public function execute(): void;

    public function getReport(): Report;
}
