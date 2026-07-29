<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Service\CollectionsAvailabilitiesService;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use App\Service\GamesAvailabilitiesService;
use App\Service\GamesShiniesAvailabilitiesService;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class DexPokemonAvailabilityCalculator
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        private readonly GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
        private readonly GamesAvailabilitiesService $gamesAvailabilitiesService,
        private readonly GamesShiniesAvailabilitiesService $gamesShiniesAvailabilitiesService,
        private readonly CollectionsAvailabilitiesService $collectionsAvailabilitiesService,
    ) {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * ExpressionLanguage caches every parsed rule forever in memory (no cache
     * pool given = ArrayAdapter with no eviction). Since each Dex has its own
     * (potentially very large) selectionRule, that cache must be dropped
     * between dexes or it grows unbounded across a full recalculation run.
     */
    public function resetExpressionLanguageCache(): void
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function calculate(Dex $dex, Pokemon $pokemon): ?DexAvailability
    {
        $rule = $dex->selectionRule;

        $values = $this->getValues($rule, $pokemon);

        /** @var bool $isGettable */
        $isGettable = $this->expressionLanguage->evaluate($rule, $values);

        if (!$isGettable) {
            return null;
        }

        return DexAvailability::create($pokemon, $dex);
    }

    /**
     * @return mixed[][]
     */
    private function getValues(string $rule, Pokemon $pokemon): array
    {
        $values = [];

        $this->setPokemonValues($values, $rule, $pokemon);
        $this->setBundlesValues($values, $rule, $pokemon);
        $this->setGamesValues($values, $rule, $pokemon);
        $this->setCollectionsValues($values, $rule, $pokemon);

        /** @var mixed[][] $values */
        return $values;
    }

    /**
     * @param mixed[] $values
     */
    private function setPokemonValues(array &$values, string $rule, Pokemon $pokemon): void
    {
        if (str_contains($rule, 'p.') || str_contains($rule, 'p?.')) {
            $values['p'] = $pokemon;
        }
    }

    /**
     * @param mixed[] $values
     */
    private function setBundlesValues(array &$values, string $rule, Pokemon $pokemon): void
    {
        if (str_contains($rule, 'ba.') || str_contains($rule, 'ba?.')) {
            $values['ba'] = $this->gameBundlesAvailabilitiesService->getFromPokemon($pokemon);
        }

        if (str_contains($rule, 'bsa.') || str_contains($rule, 'bsa?.')) {
            $values['bsa'] = $this->gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon);
        }
    }

    /**
     * @param mixed[] $values
     */
    private function setGamesValues(array &$values, string $rule, Pokemon $pokemon): void
    {
        if (str_contains($rule, 'ga.') || str_contains($rule, 'ga?.')) {
            $values['ga'] = $this->gamesAvailabilitiesService->getFromPokemon($pokemon);
        }

        if (str_contains($rule, 'gsa.') || str_contains($rule, 'gsa?.')) {
            $values['gsa'] = $this->gamesShiniesAvailabilitiesService->getFromPokemon($pokemon);
        }
    }

    /**
     * @param mixed[] $values
     */
    private function setCollectionsValues(array &$values, string $rule, Pokemon $pokemon): void
    {
        if (str_contains($rule, 'ca.') || str_contains($rule, 'ca?.')) {
            $values['ca'] = $this->collectionsAvailabilitiesService->getFromPokemon($pokemon);
        }
    }
}
