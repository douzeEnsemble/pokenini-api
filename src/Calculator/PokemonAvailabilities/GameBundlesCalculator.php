<?php

declare(strict_types=1);

namespace App\Calculator\PokemonAvailabilities;

use App\Calculator\AbstractCalculator;
use App\Entity\PokemonAvailabilities;
use App\Repository\PokemonAvailabilitiesRepository;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GameBundlesCalculator extends AbstractCalculator
{
    protected string $statisticName = 'pokemon_availabilities_game_bundle';

    public function __construct(
        private readonly PokemonAvailabilitiesRepository $repository,
    ) {}

    #[\Override]
    public function execute(): void
    {
        $this->init();

        $this->repository->removeAllByCategory(PokemonAvailabilities::CATEGORY_GAME_BUNDLE);

        $count = $this->repository->calculateGameBundle();

        $this->statictic->incrementBy($count);
    }
}
