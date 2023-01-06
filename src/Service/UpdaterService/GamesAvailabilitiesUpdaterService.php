<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\GamesAvailabilitiesUpdater;

class GamesAvailabilitiesUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GamesAvailabilitiesUpdater $gamesAvailabilitiesUpdater
    ) {
    }

    public function execute(): void
    {
        $this->gamesAvailabilitiesUpdater->execute();

        $this->report = new Report([
            $this->gamesAvailabilitiesUpdater->getStatistic(),
        ]);
    }
}
