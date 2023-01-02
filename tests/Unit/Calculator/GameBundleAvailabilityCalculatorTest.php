<?php

namespace App\Tests\Unit\Calculator;

use App\Calculator\GameBundleAvailabilityCalculator;
use App\Repository\GameBundleAvailabilityRepository;
use PHPUnit\Framework\TestCase;

class GameBundleAvailabilityCalculatorTest extends TestCase
{
    public function testExecute(): void
    {
        $gameBundleAvailabilityRepository = $this->createMock(GameBundleAvailabilityRepository::class);
        $gameBundleAvailabilityRepository
            ->expects($this->once())
            ->method('removeAll')
        ;
        $gameBundleAvailabilityRepository
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(12)
        ;

        $service = new GameBundleAvailabilityCalculator($gameBundleAvailabilityRepository);
        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(12, $statistic->count);
    }
}
