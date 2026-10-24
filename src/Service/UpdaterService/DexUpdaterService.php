<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\DexCategoriesUpdater;
use App\Updater\DexUpdater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DexUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly DexCategoriesUpdater $dexCategoriesUpdater,
        private readonly DexUpdater $dexUpdater,
    ) {}

    #[\Override]
    public function execute(?string $sheetName = null): void
    {
        $this->dexCategoriesUpdater->execute();
        $this->dexUpdater->execute();

        $this->report = new Report([
            $this->dexCategoriesUpdater->getStatistic(),
            $this->dexUpdater->getStatistic(),
        ]);
    }
}
