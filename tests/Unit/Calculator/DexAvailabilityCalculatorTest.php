<?php

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexAvailabilityCalculator;
use App\DTO\GameBundlesAvailabilities;
use App\Entity\Dex;
use App\Entity\GameBundle;
use App\Entity\GameBundleAvailability;
use App\Entity\Pokemon;
use App\Repository\DexAvailabilityRepository;
use App\Repository\DexRepository;
use App\Repository\PokemonRepository;
use App\Service\GameBundleAvailabilityService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DexAvailabilityCalculatorTest extends TestCase
{
    public function testExecute(): void
    {
        $pokemonA = new Pokemon();
        $pokemonB = new Pokemon();
        $pokemonC = new Pokemon();

        $dexAvailabilityRepository = $this->createMock(DexAvailabilityRepository::class);
        $dexAvailabilityRepository
            ->expects($this->once())
            ->method('removeAll')
        ;

        $gameBundleAvailabilityService = $this->createMock(GameBundleAvailabilityService::class);
        $gameBundleAvailabilityService
            ->expects($this->exactly(6))
            ->method('getFromPokemon')
            ->willReturn(new GameBundlesAvailabilities([]))
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(3))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(2))
            ->method('flush')
        ;
        $entityManager
            ->expects($this->exactly(2))
            ->method('clear')
        ;

        $dexOne = new Dex();
        $dexOne->selectionRule = 'true';
        $dexTwo = new Dex();
        $dexTwo->selectionRule = 'false';

        $dexQuery = $this->createMock(AbstractQuery::class);
        $dexQuery
            ->expects($this->once())
            ->method('toIterable')
            ->willReturn([
                $dexOne,
                $dexTwo,
            ])
        ;
        $dexRepository = $this->createMock(DexRepository::class);
        $dexRepository
            ->expects($this->once())
            ->method('getQueryAll')
            ->willReturn($dexQuery)
        ;

        $pokemonQuery = $this->createMock(AbstractQuery::class);
        $pokemonQuery
            ->expects($this->exactly(2))
            ->method('toIterable')
            ->willReturn([
                $pokemonA,
                $pokemonB,
                $pokemonC,
            ])
        ;
        $pokemonRepository = $this->createMock(PokemonRepository::class);
        $pokemonRepository
            ->expects($this->exactly(2))
            ->method('getQueryAll')
            ->willReturn($pokemonQuery)
        ;

        $service = new DexAvailabilityCalculator(
            $dexAvailabilityRepository,
            $gameBundleAvailabilityService,
            $dexRepository,
            $pokemonRepository,
            $entityManager
        );

        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(3, $statistic->count);
    }
}
