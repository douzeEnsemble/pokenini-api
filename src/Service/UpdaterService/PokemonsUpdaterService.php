<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\PokemonUpdater;

class PokemonsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly PokemonUpdater $pokemonUpdater
    ) {
    }

    public function execute(): void
    {
        $this->pokemonUpdater->execute();

        $this->report = new Report([
            $this->pokemonUpdater->getStatistic(),
        ]);
    }
}
