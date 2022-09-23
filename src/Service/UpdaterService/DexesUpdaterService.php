<?php

namespace App\Service\UpdaterService;

use App\Updater\DexUpdater;

class DexesUpdaterService implements UpdaterServiceInterface
{
    public function __construct(private readonly DexUpdater $dexUpdater)
    {
    }

    public function execute(): void
    {
        $this->dexUpdater->execute();
    }
}
