<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\RegionalDexNumberUpdater;

class RegionalDexNumbersUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly RegionalDexNumberUpdater $regionalDexNumberUpdater
    ) {
    }

    public function execute(): void
    {
        $this->regionalDexNumberUpdater->execute();

        $this->report = new Report([
            $this->regionalDexNumberUpdater->getStatistic(),
        ]);
    }
}
