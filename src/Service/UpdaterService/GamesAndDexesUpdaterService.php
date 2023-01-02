<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

class GamesAndDexesUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GamesUpdaterService $gamesUpdaterService,
        private readonly DexesUpdaterService $dexesUpdaterService,
    ) {
    }

    public function execute(): void
    {
        $this->gamesUpdaterService->execute();
        $this->dexesUpdaterService->execute();

        $this->report = new Report([]);
        $this->report->merge($this->gamesUpdaterService->getReport());
        $this->report->merge($this->dexesUpdaterService->getReport());
    }
}
