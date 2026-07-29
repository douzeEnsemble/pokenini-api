<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Dex;
use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use Doctrine\ORM\EntityManagerInterface;

class DexAvailabilityCalculator
{
    public function __construct(
        private readonly PokemonsRepository $pokemonsRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DexPokemonAvailabilityCalculator $dexPokemonAvailabilityCalculator,
        private readonly int $batchSize = 100,
    ) {}

    public function calculate(Dex $dex): int
    {
        $this->dexPokemonAvailabilityCalculator->resetExpressionLanguageCache();

        $count = 0;
        $batchCount = 0;
        $dexId = $dex->getIdentifier();

        $pokemonQuery = $this->pokemonsRepository->getQueryAll();

        /** @var Pokemon $pokemon */
        foreach ($pokemonQuery->toIterable() as $pokemon) {
            $dexAvailability = $this->dexPokemonAvailabilityCalculator->calculate($dex, $pokemon);

            if (null === $dexAvailability) {
                continue;
            }

            $this->entityManager->persist($dexAvailability);
            ++$count;

            if (++$batchCount >= $this->batchSize) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $batchCount = 0;

                /** @var Dex $dex */
                $dex = $this->entityManager->getReference(Dex::class, $dexId);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $count;
    }
}
