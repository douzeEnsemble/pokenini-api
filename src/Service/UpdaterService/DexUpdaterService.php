<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\DexUpdater;

class DexUpdaterService extends AbstractUpdaterService
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
