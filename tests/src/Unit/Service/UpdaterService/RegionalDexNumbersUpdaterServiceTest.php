<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Updater\RegionalDexNumbersUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegionalDexNumbersUpdaterService::class)]
class RegionalDexNumbersUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $service = $this->getService();

        $service->execute();
    }

    public function testGetReport(): void
    {
        $service = $this->getService();

        $service->execute();
        $report = $service->getReport();

        $this->assertInstanceOf(Report::class, $report);
        $this->assertInstanceOf(Statistic::class, $report->detail[0]);
    }

    private function getService(): RegionalDexNumbersUpdaterService
    {
        $regionalDexNumbersUpdater = $this->createMock(RegionalDexNumbersUpdater::class);
        $regionalDexNumbersUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionalDexNumbersUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('rdn'))
        ;

        return new RegionalDexNumbersUpdaterService(
            $regionalDexNumbersUpdater
        );
    }
}
