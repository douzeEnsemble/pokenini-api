<?php

declare(strict_types=1);

namespace App\Calculator\PokemonAvailabilities;

use App\Calculator\AbstractCalculator;
use App\Entity\PokemonAvailabilities;
use App\Repository\PokemonAvailabilitiesRepository;

class GameBundlesShinyCalculator extends AbstractCalculator
{
    protected string $statisticName = 'pokemon_availabilities_game_bundle_shiny';

    public function __construct(
        private readonly PokemonAvailabilitiesRepository $repository,
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->init();

        $this->repository->removeAllByCategory(PokemonAvailabilities::CATEGORY_GAME_BUNDLE_SHINY);

        $count = $this->repository->calculateGameBundleShiny();

        $this->statictic->incrementBy($count);
    }
}
