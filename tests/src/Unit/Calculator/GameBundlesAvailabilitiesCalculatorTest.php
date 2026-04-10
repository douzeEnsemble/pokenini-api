<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\AbstractCalculator;
use App\Calculator\GameBundlesAvailabilitiesCalculator;
use App\Repository\GameBundlesAvailabilitiesRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesAvailabilitiesCalculator::class)]
#[CoversClass(AbstractCalculator::class)]
final class GameBundlesAvailabilitiesCalculatorTest extends TestCase
{
    public function testInit(): void
    {
        $gameBundlesAvailabilitiesRepository = $this->createMock(GameBundlesAvailabilitiesRepository::class);
        $gameBundlesAvailabilitiesRepository
            ->expects($this->never())
            ->method('removeAll')
        ;
        $gameBundlesAvailabilitiesRepository
            ->expects($this->never())
            ->method('calculate')
        ;

        $service = new GameBundlesAvailabilitiesCalculator($gameBundlesAvailabilitiesRepository);

        $service->init();

        $this->assertSame('game_bundles_availabilities', $service->getStatistic()->slug);
        $this->assertSame(0, $service->getStatistic()->count);
    }

    public function testExecute(): void
    {
        $gameBundlesAvailabilitiesRepository = $this->createMock(GameBundlesAvailabilitiesRepository::class);
        $gameBundlesAvailabilitiesRepository
            ->expects($this->once())
            ->method('removeAll')
        ;
        $gameBundlesAvailabilitiesRepository
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(12)
        ;

        $service = new GameBundlesAvailabilitiesCalculator($gameBundlesAvailabilitiesRepository);
        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(12, $statistic->count);
    }

    public function testExecuteTwice(): void
    {
        $gameBundlesAvailabilitiesRepository = $this->createMock(GameBundlesAvailabilitiesRepository::class);
        $gameBundlesAvailabilitiesRepository
            ->expects($this->exactly(2))
            ->method('removeAll')
        ;
        $gameBundlesAvailabilitiesRepository
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(12)
        ;

        $service = new GameBundlesAvailabilitiesCalculator($gameBundlesAvailabilitiesRepository);

        $service->execute();

        $firstCount = $service->getStatistic()->count;

        $service->execute();

        $this->assertEquals($firstCount, $service->getStatistic()->count);
    }
}
