<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Repository\GameBundlesShiniesAvailabilitiesRepository;

class GameBundlesShiniesAvailabilitiesCalculator extends AbstractCalculator
{
    protected string $statisticName = 'game_bundles_shinies_availabilities';
    public function __construct(
        private readonly GameBundlesShiniesAvailabilitiesRepository $repository,
    ) {
        parent::__construct();
    }

    public function execute(): void
    {
        $this->init();

        $this->repository->removeAll();

        $count = $this->repository->calculate();

        $this->statictic->incrementBy($count);
    }
}
