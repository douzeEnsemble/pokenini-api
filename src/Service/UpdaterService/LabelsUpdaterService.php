<?php

namespace App\Service\UpdaterService;

use App\Updater\CatchStateUpdater;

class LabelsUpdaterService implements UpdaterServiceInterface
{
    public function __construct(
        private readonly CatchStateUpdater $catchStateUpdater,
        private readonly FormsUpdaterService $formsUpdaterService,
    ) {
    }

    public function execute(): void
    {
        $this->catchStateUpdater->execute();
        $this->formsUpdaterService->execute();
    }
}
