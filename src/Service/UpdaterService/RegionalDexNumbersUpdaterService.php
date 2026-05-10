<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\RegionalDexNumbersUpdater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RegionalDexNumbersUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly RegionalDexNumbersUpdater $regionalDexNumbersUpdater
    ) {}

    #[\Override]
    public function execute(?string $sheetName = null): void
    {
        $this->regionalDexNumbersUpdater->execute();

        $this->report = new Report([
            $this->regionalDexNumbersUpdater->getStatistic(),
        ]);
    }
}
