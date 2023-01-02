<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\FormsUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Updater\CatchStateUpdater;
use App\Updater\RegionUpdater;
use PHPUnit\Framework\TestCase;

class LabelsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $formsUpdaterService = $this->createMock(FormsUpdaterService::class);
        $formsUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $formsUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(new Report([]))
        ;

        $catchStateUpdater = $this->createMock(CatchStateUpdater::class);
        $catchStateUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $catchStateUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('cs'))
        ;

        $regionUpdater = $this->createMock(RegionUpdater::class);
        $regionUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('r'))
        ;

        $service = new LabelsUpdaterService(
            $catchStateUpdater,
            $formsUpdaterService,
            $regionUpdater
        );

        $service->execute();
    }
}
