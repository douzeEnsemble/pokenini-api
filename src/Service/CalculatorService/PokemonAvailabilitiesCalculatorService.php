<?php

declare(strict_types=1);

namespace App\Service\CalculatorService;

use App\Calculator\PokemonAvailabilities\GameBundlesCalculator;
use App\Calculator\PokemonAvailabilities\GameBundlesShinyCalculator;
use App\DTO\DataChangeReport\Report;

class PokemonAvailabilitiesCalculatorService extends AbstractCalculatorService
{
    public function __construct(
        private readonly GameBundlesCalculator $gameBundlesCalculator,
        private readonly GameBundlesShinyCalculator $gameBundlesShinyCalculator,
    ) {
    }

    public function execute(): void
    {
        $this->gameBundlesCalculator->execute();
        $this->gameBundlesShinyCalculator->execute();

        $this->report = new Report([
            $this->gameBundlesCalculator->getStatistic(),
            $this->gameBundlesShinyCalculator->getStatistic(),
        ]);
    }
}
