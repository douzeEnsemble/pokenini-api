<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GamesCollectionsAndDexUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly GamesUpdaterService $gamesUpdaterService,
        private readonly DexUpdaterService $dexUpdaterService,
        private readonly CollectionsUpdaterService $collectionsUpdaterService,
    ) {}

    #[\Override]
    public function execute(?string $sheetName = null): void
    {
        $this->gamesUpdaterService->execute();
        $this->dexUpdaterService->execute();
        $this->collectionsUpdaterService->execute();

        $this->report = new Report([]);
        $this->report->merge($this->gamesUpdaterService->getReport());
        $this->report->merge($this->dexUpdaterService->getReport());
        $this->report->merge($this->collectionsUpdaterService->getReport());
    }
}
