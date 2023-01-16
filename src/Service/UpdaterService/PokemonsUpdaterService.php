<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\PokemonsUpdater;

class PokemonsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly PokemonsUpdater $pokemonsUpdater
    ) {
    }

    public function execute(): void
    {
        $this->pokemonsUpdater->execute();

        $this->report = new Report([
            $this->pokemonsUpdater->getStatistic(),
        ]);
    }
}
