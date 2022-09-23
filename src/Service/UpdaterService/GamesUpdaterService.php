<?php

namespace App\Service\UpdaterService;

use App\Updater\GameBundleUpdater;
use App\Updater\GameGenerationUpdater;
use App\Updater\GameUpdater;

class GamesUpdaterService implements UpdaterServiceInterface
{
    public function __construct(
        private readonly GameGenerationUpdater $gameGenerationUpdater,
        private readonly GameBundleUpdater $gameBundleUpdater,
        private readonly GameUpdater $gameUpdater,
    ) {
    }

    public function execute(): void
    {
        $this->gameGenerationUpdater->execute();
        $this->gameBundleUpdater->execute();
        $this->gameUpdater->execute();
    }
}
