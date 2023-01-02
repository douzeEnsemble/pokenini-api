<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\CatchStateUpdater;
use App\Updater\RegionUpdater;

class LabelsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly CatchStateUpdater $catchStateUpdater,
        private readonly FormsUpdaterService $formsUpdaterService,
        private readonly RegionUpdater $regionsUpdater,
    ) {
    }

    public function execute(): void
    {
        $this->catchStateUpdater->execute();
        $this->formsUpdaterService->execute();
        $this->regionsUpdater->execute();

        $this->report = new Report([
            $this->catchStateUpdater->getStatistic(),
            $this->regionsUpdater->getStatistic(),
        ]);

        $this->report->merge($this->formsUpdaterService->getReport());
    }
}
