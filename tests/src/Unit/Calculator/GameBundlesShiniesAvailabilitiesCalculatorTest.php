<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calculator;

use App\Calculator\GameBundlesShiniesAvailabilitiesCalculator;
use App\Repository\GameBundlesShiniesAvailabilitiesRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesShiniesAvailabilitiesCalculator::class)]
final class GameBundlesShiniesAvailabilitiesCalculatorTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $gameBundlesShiniesAvailabilitiesRepository
            = $this->createMock(GameBundlesShiniesAvailabilitiesRepository::class);
        $gameBundlesShiniesAvailabilitiesRepository
            ->expects($this->once())
            ->method('removeAll')
        ;
        $gameBundlesShiniesAvailabilitiesRepository
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(12)
        ;

        $service = new GameBundlesShiniesAvailabilitiesCalculator(
            $gameBundlesShiniesAvailabilitiesRepository
        );
        $service->execute();
        $statistic = $service->getStatistic();

        $this->assertEquals(12, $statistic->count);
    }

    #[Test]
    public function executeTwice(): void
    {
        $gameBundlesShiniesAvailabilitiesRepository = $this->createMock(
            GameBundlesShiniesAvailabilitiesRepository::class
        );
        $gameBundlesShiniesAvailabilitiesRepository
            ->expects($this->exactly(2))
            ->method('removeAll')
        ;
        $gameBundlesShiniesAvailabilitiesRepository
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(12)
        ;

        $service = new GameBundlesShiniesAvailabilitiesCalculator($gameBundlesShiniesAvailabilitiesRepository);

        $service->execute();

        $firstCount = $service->getStatistic()->count;

        $service->execute();

        $this->assertEquals($firstCount, $service->getStatistic()->count);
    }
}
