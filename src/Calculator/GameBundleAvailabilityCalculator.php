<?php

namespace App\Calculator;

use App\Repository\GameBundleAvailabilityRepository;

class GameBundleAvailabilityCalculator extends AbstractCalculator
{
    protected string $statisticName = 'game_bundle_availability';
    public function __construct(
        private readonly GameBundleAvailabilityRepository $gameBundleAvailabilityRepository,
    ) {
        parent::__construct();
    }

    public function execute(): void
    {
        $this->gameBundleAvailabilityRepository->removeAll();

        $count = $this->gameBundleAvailabilityRepository->calculate();

        $this->statictic->incrementBy($count);
    }
}
