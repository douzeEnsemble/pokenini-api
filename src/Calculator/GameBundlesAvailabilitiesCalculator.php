<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Repository\GameBundlesAvailabilitiesRepository;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GameBundlesAvailabilitiesCalculator extends AbstractCalculator
{
    protected string $statisticName = 'game_bundles_availabilities';

    public function __construct(
        private readonly GameBundlesAvailabilitiesRepository $repository,
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->init();

        $this->repository->removeAll();

        $count = $this->repository->calculate();

        $this->statistic->incrementBy($count);
    }
}
