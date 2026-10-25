<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\FormsUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Updater\CatchStatesUpdater;
use App\Updater\DexCategoriesUpdater;
use App\Updater\RegionsUpdater;
use App\Updater\TypesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LabelsUpdaterService::class)]
final class LabelsUpdaterServiceTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $service = $this->getService();

        $service->execute();
    }

    #[Test]
    public function getReport(): void
    {
        $service = $this->getService();

        $service->execute();
        $report = $service->getReport();

        $this->assertSame('cs', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
        $this->assertSame('dc', $report->detail[1]->slug);
        $this->assertSame('r', $report->detail[2]->slug);
        $this->assertSame('t', $report->detail[3]->slug);
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

        $dexCategoriesUpdater = $this->createMock(DexCategoriesUpdater::class);
        $dexCategoriesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $dexCategoriesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('dc'))
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
            $dexCategoriesUpdater,
            $formsUpdaterService,
            $regionsUpdater,
            $typesUpdater,
        );
    }
}
