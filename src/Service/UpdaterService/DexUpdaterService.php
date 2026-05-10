<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\DexUpdater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DexUpdaterService extends AbstractUpdaterService
{
    public function __construct(private readonly DexUpdater $dexUpdater) {}

    #[\Override]
    public function execute(?string $sheetName = null): void
    {
        $this->dexUpdater->execute();

        $this->report = new Report([
            $this->dexUpdater->getStatistic(),
        ]);
    }
}
