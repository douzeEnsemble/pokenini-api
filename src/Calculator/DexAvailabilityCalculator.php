<?php

namespace App\Calculator;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Repository\DexAvailabilityRepository;
use App\Repository\DexRepository;
use App\Repository\PokemonRepository;
use App\Service\GameBundleAvailabilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class DexAvailabilityCalculator extends AbstractCalculator
{
    protected string $statisticName = 'dex_availability';
    public function __construct(
        private readonly DexAvailabilityRepository $dexAvailabilityRepository,
        private readonly GameBundleAvailabilityService $gameBundleAvailabilityService,
        private readonly DexRepository $dexRepository,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(): void
    {
        $this->dexAvailabilityRepository->removeAll();

        $dexQuery = $this->dexRepository->getQueryAll();

        $expressionLanguage = new ExpressionLanguage();

        /** @var Dex $dex */
        foreach ($dexQuery->toIterable() as $dex) {
            $pokemonQuery = $this->pokemonRepository->getQueryAll();
            /** @var Pokemon $pokemon */
            foreach ($pokemonQuery->toIterable() as $pokemon) {
                $isGettable = $expressionLanguage->evaluate(
                    $dex->selectionRule,
                    [
                        'p' => $pokemon,
                        'ba' => $this->gameBundleAvailabilityService->getFromPokemon($pokemon),
                    ]
                );

                if (!$isGettable) {
                    continue;
                }

                $dexAvailability = DexAvailability::create($pokemon, $dex);

                $this->entityManager->persist($dexAvailability);

                $this->statictic->increment();
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
