<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\DexRepository;
use App\Repository\PokemonsRepository;
use App\Service\GameBundlesAvailabilitiesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class DexAvailabilitiesCalculator extends AbstractCalculator
{
    protected string $statisticName = 'dex_availabilities';
    public function __construct(
        private readonly DexAvailabilitiesRepository $dexAvailabilitiesRepo,
        private readonly GameBundlesAvailabilitiesService $gameBundlesAvailabilitiesService,
        private readonly DexRepository $dexRepository,
        private readonly PokemonsRepository $pokemonsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(): void
    {
        $this->dexAvailabilitiesRepo->removeAll();

        $dexQuery = $this->dexRepository->getQueryAll();

        $expressionLanguage = new ExpressionLanguage();

        /** @var Dex $dex */
        foreach ($dexQuery->toIterable() as $dex) {
            $pokemonQuery = $this->pokemonsRepository->getQueryAll();
            /** @var Pokemon $pokemon */
            foreach ($pokemonQuery->toIterable() as $pokemon) {
                $isGettable = $expressionLanguage->evaluate(
                    $dex->selectionRule,
                    [
                        'p' => $pokemon,
                        'ba' => $this->gameBundlesAvailabilitiesService->getFromPokemon($pokemon),
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
