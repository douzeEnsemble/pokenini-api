<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

interface UpdaterServiceInterface
{
    public function execute(?string $sheetName = null): void;

    public function getReport(): Report;
}
