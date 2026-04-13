<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\DexUpdaterService;
use App\Updater\DexUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexUpdaterService::class)]
final class DexUpdaterServiceTest extends TestCase
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

        $this->assertSame('d', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
    }

    private function getService(): DexUpdaterService
    {
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

        return new DexUpdaterService($dexUpdater);
    }
}
