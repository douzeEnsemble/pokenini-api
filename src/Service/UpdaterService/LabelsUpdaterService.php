<?php

declare(strict_types=1);

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\CatchStatesUpdater;
use App\Updater\RegionsUpdater;
use App\Updater\TypesUpdater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LabelsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly CatchStatesUpdater $catchStatesUpdater,
        private readonly FormsUpdaterService $formsUpdaterService,
        private readonly RegionsUpdater $regionsUpdater,
        private readonly TypesUpdater $typesUpdater,
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->catchStatesUpdater->execute();
        $this->formsUpdaterService->execute();
        $this->regionsUpdater->execute();
        $this->typesUpdater->execute();

        $this->report = new Report([
            $this->catchStatesUpdater->getStatistic(),
            $this->regionsUpdater->getStatistic(),
            $this->typesUpdater->getStatistic(),
        ]);

        $this->report->merge($this->formsUpdaterService->getReport());
    }
}
