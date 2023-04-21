<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\GamesShiniesAvailabilitiesUpdater;

class GamesShiniesAvailabilitiesUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GamesShiniesAvailabilitiesUpdater $updater
    ) {
    }

    public function execute(): void
    {
        $this->updater->execute();

        $this->report = new Report([
            $this->updater->getStatistic(),
        ]);
    }
}
