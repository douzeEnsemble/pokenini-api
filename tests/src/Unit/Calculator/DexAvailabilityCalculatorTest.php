<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexAvailabilityCalculator;
use App\Calculator\DexPokemonAvailabilityCalculator;
use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilityCalculator::class)]
#[CoversClass(DexAvailability::class)]
class DexAvailabilityCalculatorTest extends TestCase
{
    public function testCalculate(): void
    {
        $pokemonA = new Pokemon();
        $pokemonB = new Pokemon();
        $pokemonC = new Pokemon();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(3))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $entityManager
            ->expects($this->once())
            ->method('clear')
        ;

        $dex = new Dex();

        $pokemonQuery = $this->createMock(AbstractQuery::class);
        $pokemonQuery
            ->expects($this->once())
            ->method('toIterable')
            ->willReturn([
                $pokemonA,
                $pokemonB,
                $pokemonC,
            ])
        ;
        $pokemonsRepository = $this->createMock(PokemonsRepository::class);
        $pokemonsRepository
            ->expects($this->once())
            ->method('getQueryAll')
            ->willReturn($pokemonQuery)
        ;

        $dexPokemonAvailabilityCalculator = $this->createMock(DexPokemonAvailabilityCalculator::class);
        $dexPokemonAvailabilityCalculator
            ->expects($this->exactly(3))
            ->method('calculate')
            ->willReturnOnConsecutiveCalls(
                DexAvailability::create($pokemonA, $dex),
                DexAvailability::create($pokemonB, $dex),
                DexAvailability::create($pokemonC, $dex),
            )
        ;

        $calculator = new DexAvailabilityCalculator(
            $pokemonsRepository,
            $entityManager,
            $dexPokemonAvailabilityCalculator,
        );

        $count = $calculator->calculate($dex);

        $this->assertEquals(3, $count);
    }

    public function testCalculateTwice(): void
    {
        $pokemonA = new Pokemon();
        $pokemonB = new Pokemon();
        $pokemonC = new Pokemon();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(6))
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

        $dex = new Dex();

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

        $dexPokemonAvailabilityCalculator = $this->createMock(DexPokemonAvailabilityCalculator::class);
        $dexPokemonAvailabilityCalculator
            ->expects($this->exactly(6))
            ->method('calculate')
            ->willReturnOnConsecutiveCalls(
                DexAvailability::create($pokemonA, $dex),
                DexAvailability::create($pokemonB, $dex),
                DexAvailability::create($pokemonC, $dex),
                DexAvailability::create($pokemonA, $dex),
                DexAvailability::create($pokemonB, $dex),
                DexAvailability::create($pokemonC, $dex),
            )
        ;

        $calculator = new DexAvailabilityCalculator(
            $pokemonsRepository,
            $entityManager,
            $dexPokemonAvailabilityCalculator,
        );

        $firstCount = $calculator->calculate($dex);

        $count = $calculator->calculate($dex);

        $this->assertEquals($firstCount, $count);
    }

    public function testCalculateWithoutAvailabilities(): void
    {
        $pokemonA = new Pokemon();
        $pokemonB = new Pokemon();
        $pokemonC = new Pokemon();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->never())
            ->method('persist')
        ;
        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $entityManager
            ->expects($this->once())
            ->method('clear')
        ;

        $dex = new Dex();

        $pokemonQuery = $this->createMock(AbstractQuery::class);
        $pokemonQuery
            ->expects($this->once())
            ->method('toIterable')
            ->willReturn([
                $pokemonA,
                $pokemonB,
                $pokemonC,
            ])
        ;
        $pokemonsRepository = $this->createMock(PokemonsRepository::class);
        $pokemonsRepository
            ->expects($this->once())
            ->method('getQueryAll')
            ->willReturn($pokemonQuery)
        ;

        $dexPokemonAvailabilityCalculator = $this->createMock(DexPokemonAvailabilityCalculator::class);
        $dexPokemonAvailabilityCalculator
            ->expects($this->exactly(3))
            ->method('calculate')
            ->willReturnOnConsecutiveCalls(
                null,
                null,
                null,
            )
        ;

        $calculator = new DexAvailabilityCalculator(
            $pokemonsRepository,
            $entityManager,
            $dexPokemonAvailabilityCalculator,
        );

        $count = $calculator->calculate($dex);

        $this->assertEquals(0, $count);
    }
}
