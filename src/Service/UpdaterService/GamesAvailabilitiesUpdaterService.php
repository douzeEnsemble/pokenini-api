<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\GamesAvailabilitiesUpdater;

class GamesAvailabilitiesUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GamesAvailabilitiesUpdater $updater
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->updater->execute();

        $this->report = new Report([
            $this->updater->getStatistic(),
        ]);
    }
}
