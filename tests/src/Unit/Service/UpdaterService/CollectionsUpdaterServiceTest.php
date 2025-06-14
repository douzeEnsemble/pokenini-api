<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\CollectionsUpdaterService;
use App\Updater\CollectionsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsUpdaterService::class)]
class CollectionsUpdaterServiceTest extends TestCase
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

    private function getService(): CollectionsUpdaterService
    {
        $collectionsUpdater = $this->createMock(CollectionsUpdater::class);
        $collectionsUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $collectionsUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('g'))
        ;

        return new CollectionsUpdaterService(
            $collectionsUpdater
        );
    }
}
