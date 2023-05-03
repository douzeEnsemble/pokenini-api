<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Service\GameBundlesAvailabilitiesService;
use App\Service\GameBundlesShiniesAvailabilitiesService;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class DexPokemonAvailabilityCalculator
{
    private readonly ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        private readonly GameBundlesShiniesAvailabilitiesService $gameBundlesShiniesAvailabilitiesService,
    ) {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function calculate(Dex $dex, Pokemon $pokemon): ?DexAvailability
    {
        $rule = $dex->selectionRule;
        $values = [];

        if (false !== strpos($rule, 'p.') || false !== strpos($rule, 'p?.')) {
            $values['p'] = $pokemon;
        }

        if (false !== strpos($rule, 'ba.') || false !== strpos($rule, 'ba?.')) {
            $values['ba'] = $this->gameBundlesAvailabilitiesService->getFromPokemon($pokemon);
        }

        if (false !== strpos($rule, 'bsa.') || false !== strpos($rule, 'bsa?.')) {
            $values['bsa'] = $this->gameBundlesShiniesAvailabilitiesService->getFromPokemon($pokemon);
        }

        $isGettable = $this->expressionLanguage->evaluate($rule, $values);

        if (!$isGettable) {
            return null;
        }

        return DexAvailability::create($pokemon, $dex);
    }
}
