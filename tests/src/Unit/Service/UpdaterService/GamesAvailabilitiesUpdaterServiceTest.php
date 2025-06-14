<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use App\Updater\GamesAvailabilitiesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesAvailabilitiesUpdaterService::class)]
class GamesAvailabilitiesUpdaterServiceTest extends TestCase
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

    private function getService(): GamesAvailabilitiesUpdaterService
    {
        $gamesAvailabilitiesUpdater = $this->createMock(GamesAvailabilitiesUpdater::class);
        $gamesAvailabilitiesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gamesAvailabilitiesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('ga'))
        ;

        return new GamesAvailabilitiesUpdaterService(
            $gamesAvailabilitiesUpdater
        );
    }
}
