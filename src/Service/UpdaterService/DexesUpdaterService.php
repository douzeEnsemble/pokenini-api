<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\DexUpdater;

class DexesUpdaterService extends AbstractUpdaterService
{
    public function __construct(private readonly DexUpdater $dexUpdater)
    {
    }

    public function execute(): void
    {
        $this->dexUpdater->execute();

        $this->report = new Report([
            $this->dexUpdater->getStatistic(),
        ]);
    }
}
