<?php

namespace App\Tests\Unit\Calculator;

use App\Calculator\GameBundlesAvailabilitiesCalculator;
use App\Repository\GameBundlesAvailabilitiesRepository;
use PHPUnit\Framework\TestCase;

class GameBundlesAvailabilitiesCalculatorTest extends TestCase
{
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
}
