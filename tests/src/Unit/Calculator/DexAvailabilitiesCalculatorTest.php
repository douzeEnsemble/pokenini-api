<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\AbstractCalculator;
use App\Calculator\DexAvailabilitiesCalculator;
use App\Calculator\DexAvailabilityCalculator;
use App\DTO\DataChangeReport\Statistic;
use App\Entity\Dex;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\DexRepository;
use Doctrine\ORM\AbstractQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesCalculator::class)]
#[CoversClass(AbstractCalculator::class)]
class DexAvailabilitiesCalculatorTest extends TestCase
{
    public function testInit(): void
    {
        $dexAvailabilitiesRepository = $this->createMock(DexAvailabilitiesRepository::class);

        $dexRepository = $this->createMock(DexRepository::class);

        $dexAvailabilityCalculator = $this->createMock(DexAvailabilityCalculator::class);

        $service = new DexAvailabilitiesCalculator(
            $dexAvailabilitiesRepository,
            $dexRepository,
            $dexAvailabilityCalculator,
        );

        $service->init();

        $this->assertInstanceOf(Statistic::class, $service->getStatistic());
    }

    public function testExecute(): void
    {
        $dexAvailabilitiesRepository = $this->createMock(DexAvailabilitiesRepository::class);
        $dexAvailabilitiesRepository
            ->expects($this->once())
            ->method('removeAll')
        ;

        $dexQuery = $this->createMock(AbstractQuery::class);
        $dexQuery
            ->expects($this->once())
            ->method('toIterable')
            ->willReturn([
                new Dex(),
                new Dex(),
            ])
        ;
        $dexRepository = $this->createMock(DexRepository::class);
        $dexRepository
            ->expects($this->once())
            ->method('getQueryAll')
            ->willReturn($dexQuery)
        ;

        $dexAvailabilityCalculator = $this->createMock(DexAvailabilityCalculator::class);
        $dexAvailabilityCalculator
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(3)
        ;

        $service = new DexAvailabilitiesCalculator(
            $dexAvailabilitiesRepository,
            $dexRepository,
            $dexAvailabilityCalculator,
        );

        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(6, $statistic->count);
    }

    public function testExecuteTwice(): void
    {
        $dexAvailabilitiesRepository = $this->createMock(DexAvailabilitiesRepository::class);
        $dexAvailabilitiesRepository
            ->expects($this->exactly(2))
            ->method('removeAll')
        ;

        $dexQuery = $this->createMock(AbstractQuery::class);
        $dexQuery
            ->expects($this->exactly(2))
            ->method('toIterable')
            ->willReturn([
                new Dex(),
                new Dex(),
            ])
        ;
        $dexRepository = $this->createMock(DexRepository::class);
        $dexRepository
            ->expects($this->exactly(2))
            ->method('getQueryAll')
            ->willReturn($dexQuery)
        ;

        $dexAvailabilityCalculator = $this->createMock(DexAvailabilityCalculator::class);
        $dexAvailabilityCalculator
            ->expects($this->exactly(4))
            ->method('calculate')
            ->willReturn(3)
        ;

        $service = new DexAvailabilitiesCalculator(
            $dexAvailabilitiesRepository,
            $dexRepository,
            $dexAvailabilityCalculator,
        );

        $service->execute();
        $firstCount = $service->getStatistic()->count;

        $service->execute();
        $this->assertEquals($firstCount, $service->getStatistic()->count);
    }
}
