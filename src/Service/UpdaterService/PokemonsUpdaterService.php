<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\PokemonsUpdater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PokemonsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly PokemonsUpdater $pokemonsUpdater
    ) {}

    #[\Override]
    public function execute(?string $sheetName = null): void
    {
        $this->pokemonsUpdater->execute($sheetName);

        $this->report = new Report([
            $this->pokemonsUpdater->getStatistic(),
        ]);
    }
}
