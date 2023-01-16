<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\GameBundlesUpdater;
use App\Updater\GameGenerationsUpdater;
use App\Updater\GamesUpdater;

class GamesUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GameGenerationsUpdater $gameGenerationsUpdater,
        private readonly GameBundlesUpdater $gameBundlesUpdater,
        private readonly GamesUpdater $gamesUpdater,
    ) {
    }

    public function execute(): void
    {
        $this->gameGenerationsUpdater->execute();
        $this->gameBundlesUpdater->execute();
        $this->gamesUpdater->execute();

        $this->report = new Report([
            $this->gameGenerationsUpdater->getStatistic(),
            $this->gameBundlesUpdater->getStatistic(),
            $this->gamesUpdater->getStatistic(),
        ]);
    }
}
