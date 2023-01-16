<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Repository\GameBundlesAvailabilitiesRepository;

class GameBundlesAvailabilitiesCalculator extends AbstractCalculator
{
    protected string $statisticName = 'game_bundles_availabilities';
    public function __construct(
        private readonly GameBundlesAvailabilitiesRepository $gameBundlesAvailabilitiesRepo,
    ) {
        parent::__construct();
    }

    public function execute(): void
    {
        $this->gameBundlesAvailabilitiesRepo->removeAll();

        $count = $this->gameBundlesAvailabilitiesRepo->calculate();

        $this->statictic->incrementBy($count);
    }
}
