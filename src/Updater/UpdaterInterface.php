<?php

declare(strict_types=1);

namespace App\Updater;

use App\DTO\DataChangeReport\Statistic;

interface UpdaterInterface
{
    public function execute(?string $sheetName = null): void;

    public function getStatistic(): Statistic;
}
