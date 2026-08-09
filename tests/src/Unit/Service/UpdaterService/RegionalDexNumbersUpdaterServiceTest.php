<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Updater\RegionalDexNumbersUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegionalDexNumbersUpdaterService::class)]
final class RegionalDexNumbersUpdaterServiceTest extends TestCase
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

        $this->assertSame('rdn', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
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
