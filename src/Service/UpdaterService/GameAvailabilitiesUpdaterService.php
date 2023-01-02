<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\GameAvailabilityUpdater;

class GameAvailabilitiesUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GameAvailabilityUpdater $gameAvailabilityUpdater
    ) {
    }

    public function execute(): void
    {
        $this->gameAvailabilityUpdater->execute();

        $this->report = new Report([
            $this->gameAvailabilityUpdater->getStatistic(),
        ]);
    }
}
