<?php

namespace App\Service\UpdaterService;

use App\Updater\CatchStateUpdater;
use App\Updater\RegionUpdater;

class LabelsUpdaterService implements UpdaterServiceInterface
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
    }
}
