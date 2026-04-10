<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\FormsUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Updater\CatchStatesUpdater;
use App\Updater\RegionsUpdater;
use App\Updater\TypesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LabelsUpdaterService::class)]
class LabelsUpdaterServiceTest extends TestCase
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
        $service->getReport();
    }

    public function getService(): LabelsUpdaterService
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

        $catchStatesUpdater = $this->createMock(CatchStatesUpdater::class);
        $catchStatesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $catchStatesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('cs'))
        ;

        $regionsUpdater = $this->createMock(RegionsUpdater::class);
        $regionsUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionsUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('r'))
        ;

        $typesUpdater = $this->createMock(TypesUpdater::class);
        $typesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $typesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('t'))
        ;

        return new LabelsUpdaterService(
            $catchStatesUpdater,
            $formsUpdaterService,
            $regionsUpdater,
            $typesUpdater,
        );
    }
}
