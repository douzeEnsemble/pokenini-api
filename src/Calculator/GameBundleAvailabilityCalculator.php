<?php

namespace App\Calculator;

use App\Repository\GameBundleAvailabilityRepository;

class GameBundleAvailabilityCalculator implements CalculatorInterface
{
    public function __construct(
        private readonly GameBundleAvailabilityRepository $gameBundleAvailabilityRepository,
    ) {
    }

    public function execute(): int
    {
        $this->gameBundleAvailabilityRepository->removeAll();

        return $this->gameBundleAvailabilityRepository->calculate();
    }
}
