<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\DexUpdaterService;
use App\Updater\DexCategoriesUpdater;
use App\Updater\DexUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexUpdaterService::class)]
final class DexUpdaterServiceTest extends TestCase
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

        $this->assertSame('dc', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
        $this->assertSame('d', $report->detail[1]->slug);
        $this->assertSame(0, $report->detail[1]->count);
    }

    private function getService(): DexUpdaterService
    {
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

        $dexUpdater = $this->createMock(DexUpdater::class);
        $dexUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $dexUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('d'))
        ;

        return new DexUpdaterService($dexCategoriesUpdater, $dexUpdater);
    }
}
