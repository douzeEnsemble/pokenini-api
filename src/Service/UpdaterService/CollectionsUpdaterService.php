<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\CollectionsUpdater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CollectionsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly CollectionsUpdater $collectionsUpdater,
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->collectionsUpdater->execute();

        $this->report = new Report([
            $this->collectionsUpdater->getStatistic(),
        ]);
    }
}
