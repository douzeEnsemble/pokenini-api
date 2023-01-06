<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\CatchStatesUpdater;
use App\Updater\RegionsUpdater;

class LabelsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly CatchStatesUpdater $catchStatesUpdater,
        private readonly FormsUpdaterService $formsUpdaterService,
        private readonly RegionsUpdater $regionssUpdater,
    ) {
    }

    public function execute(): void
    {
        $this->catchStatesUpdater->execute();
        $this->formsUpdaterService->execute();
        $this->regionssUpdater->execute();

        $this->report = new Report([
            $this->catchStatesUpdater->getStatistic(),
            $this->regionssUpdater->getStatistic(),
        ]);

        $this->report->merge($this->formsUpdaterService->getReport());
    }
}
