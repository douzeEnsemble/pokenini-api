<?php

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexAvailabilitiesCalculator;
use App\DTO\GameBundlesAvailabilities;
use App\Entity\Dex;
use App\Entity\Pokemon;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\DexRepository;
use App\Repository\PokemonsRepository;
use App\Service\GameBundlesAvailabilitiesService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DexAvailabilitiesCalculatorTest extends TestCase
{
    public function testExecute(): void
    {
        $pokemonA = new Pokemon();
        $pokemonB = new Pokemon();
        $pokemonC = new Pokemon();

        $dexAvailabilitiesRepository = $this->createMock(DexAvailabilitiesRepository::class);
        $dexAvailabilitiesRepository
            ->expects($this->once())
            ->method('removeAll')
        ;

        $gameBundlesAvailabilitiesService = $this->createMock(GameBundlesAvailabilitiesService::class);
        $gameBundlesAvailabilitiesService
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
        $pokemonsRepository = $this->createMock(PokemonsRepository::class);
        $pokemonsRepository
            ->expects($this->exactly(2))
            ->method('getQueryAll')
            ->willReturn($pokemonQuery)
        ;

        $service = new DexAvailabilitiesCalculator(
            $dexAvailabilitiesRepository,
            $gameBundlesAvailabilitiesService,
            $dexRepository,
            $pokemonsRepository,
            $entityManager
        );

        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(3, $statistic->count);
    }
}
