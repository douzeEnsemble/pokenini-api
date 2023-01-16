<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

interface UpdaterServiceInterface
{
    public function execute(): void;

    public function getReport(): Report;
}
