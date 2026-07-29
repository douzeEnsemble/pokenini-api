<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\DexAvailabilityCalculator;
use App\Calculator\DexPokemonAvailabilityCalculator;
use App\Entity\Dex;
use App\Entity\DexAvailability;
use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilityCalculator::class)]
#[CoversClass(DexAvailability::class)]
final class DexAvailabilityCalculatorTest extends TestCase
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

        $pokemonQuery = $this->createMock(Query::class);
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
            ->expects($this->once())
            ->method('resetExpressionLanguageCache')
        ;
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

        $pokemonQuery = $this->createMock(Query::class);
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
            ->expects($this->exactly(2))
            ->method('resetExpressionLanguageCache')
        ;
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

    public function testCalculateDefaultBatchSizeDoesNotFlushWith99Items(): void
    {
        $dex = new Dex();
        $pokemons = array_fill(0, 99, new Pokemon());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(99))->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('clear');
        $entityManager->expects($this->never())->method('getReference');

        $pokemonQuery = $this->createMock(Query::class);
        $pokemonQuery->expects($this->once())->method('toIterable')->willReturn($pokemons);

        $pokemonsRepository = $this->createMock(PokemonsRepository::class);
        $pokemonsRepository->expects($this->once())->method('getQueryAll')->willReturn($pokemonQuery);

        $dexPokemonAvailabilityCalculator = $this->createMock(DexPokemonAvailabilityCalculator::class);
        $dexPokemonAvailabilityCalculator
            ->expects($this->exactly(99))
            ->method('calculate')
            ->willReturnCallback(fn (Dex $d, Pokemon $p) => DexAvailability::create($p, $d))
        ;

        $calculator = new DexAvailabilityCalculator(
            $pokemonsRepository,
            $entityManager,
            $dexPokemonAvailabilityCalculator,
        );

        $this->assertEquals(99, $calculator->calculate($dex));
    }

    public function testCalculateDefaultBatchSizeFlushesAfter100Items(): void
    {
        $dex = new Dex();
        $pokemons = array_fill(0, 100, new Pokemon());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(100))->method('persist');
        $entityManager->expects($this->exactly(2))->method('flush');
        $entityManager->expects($this->exactly(2))->method('clear');
        $entityManager
            ->expects($this->once())
            ->method('getReference')
            ->with(Dex::class)
            ->willReturn($dex)
        ;

        $pokemonQuery = $this->createMock(Query::class);
        $pokemonQuery->expects($this->once())->method('toIterable')->willReturn($pokemons);

        $pokemonsRepository = $this->createMock(PokemonsRepository::class);
        $pokemonsRepository->expects($this->once())->method('getQueryAll')->willReturn($pokemonQuery);

        $dexPokemonAvailabilityCalculator = $this->createMock(DexPokemonAvailabilityCalculator::class);
        $dexPokemonAvailabilityCalculator
            ->expects($this->exactly(100))
            ->method('calculate')
            ->willReturnCallback(fn (Dex $d, Pokemon $p) => DexAvailability::create($p, $d))
        ;

        $calculator = new DexAvailabilityCalculator(
            $pokemonsRepository,
            $entityManager,
            $dexPokemonAvailabilityCalculator,
        );

        $this->assertEquals(100, $calculator->calculate($dex));
    }

    public function testCalculateFlushesInBatches(): void
    {
        $pokemons = [new Pokemon(), new Pokemon(), new Pokemon(), new Pokemon(), new Pokemon()];
        $dex = new Dex();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(5))->method('persist');
        $entityManager->expects($this->exactly(3))->method('flush');
        $entityManager->expects($this->exactly(3))->method('clear');
        $entityManager
            ->expects($this->exactly(2))
            ->method('getReference')
            ->with(Dex::class)
            ->willReturn($dex)
        ;

        $pokemonQuery = $this->createMock(Query::class);
        $pokemonQuery
            ->expects($this->once())
            ->method('toIterable')
            ->willReturn($pokemons)
        ;
        $pokemonsRepository = $this->createMock(PokemonsRepository::class);
        $pokemonsRepository->expects($this->once())->method('getQueryAll')->willReturn($pokemonQuery);

        $dexPokemonAvailabilityCalculator = $this->createMock(DexPokemonAvailabilityCalculator::class);
        $dexPokemonAvailabilityCalculator
            ->expects($this->exactly(5))
            ->method('calculate')
            ->willReturnOnConsecutiveCalls(
                DexAvailability::create($pokemons[0], $dex),
                DexAvailability::create($pokemons[1], $dex),
                DexAvailability::create($pokemons[2], $dex),
                DexAvailability::create($pokemons[3], $dex),
                DexAvailability::create($pokemons[4], $dex),
            )
        ;

        $calculator = new DexAvailabilityCalculator(
            $pokemonsRepository,
            $entityManager,
            $dexPokemonAvailabilityCalculator,
            2,
        );

        $count = $calculator->calculate($dex);

        $this->assertEquals(5, $count);
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

        $pokemonQuery = $this->createMock(Query::class);
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
