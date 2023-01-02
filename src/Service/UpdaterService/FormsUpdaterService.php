<?php

namespace App\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Updater\Form\CategoryFormUpdater;
use App\Updater\Form\RegionalFormUpdater;
use App\Updater\Form\SpecialFormUpdater;
use App\Updater\Form\VariantFormUpdater;

class FormsUpdaterService extends AbstractUpdaterService
{
    public function __construct(
        private readonly CategoryFormUpdater $categoryFormUpdater,
        private readonly RegionalFormUpdater $regionalFormUpdater,
        private readonly SpecialFormUpdater $specialFormUpdater,
        private readonly VariantFormUpdater $variantFormUpdater
    ) {
    }

    public function execute(): void
    {
        $this->categoryFormUpdater->execute();
        $this->regionalFormUpdater->execute();
        $this->specialFormUpdater->execute();
        $this->variantFormUpdater->execute();

        $this->report = new Report([
            $this->categoryFormUpdater->getStatistic(),
            $this->regionalFormUpdater->getStatistic(),
            $this->specialFormUpdater->getStatistic(),
            $this->variantFormUpdater->getStatistic(),
        ]);
    }
}
