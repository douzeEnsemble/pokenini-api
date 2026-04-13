<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\CollectionsUpdaterService;
use App\Service\UpdaterService\DexUpdaterService;
use App\Service\UpdaterService\GamesCollectionsAndDexUpdaterService;
use App\Service\UpdaterService\GamesUpdaterService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesCollectionsAndDexUpdaterService::class)]
final class GamesCollectionsAndDexUpdaterServiceTest extends TestCase
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

        $this->assertCount(3, $report->detail);

        $this->assertSame('g', $report->detail[0]->slug);
        $this->assertSame(1, $report->detail[0]->count);

        $this->assertSame('d', $report->detail[1]->slug);
        $this->assertSame(2, $report->detail[1]->count);

        $this->assertSame('c', $report->detail[2]->slug);
        $this->assertSame(3, $report->detail[2]->count);
    }

    private function getService(): GamesCollectionsAndDexUpdaterService
    {
        $gamesUpdaterService = $this->createMock(GamesUpdaterService::class);
        $gamesUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $gamesUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(
                new Report(
                    [
                        new Statistic('g', 1),
                    ],
                )
            )
        ;

        $dexUpdaterService = $this->createMock(DexUpdaterService::class);
        $dexUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $dexUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(
                new Report(
                    [
                        new Statistic('d', 2),
                    ],
                )
            )
        ;

        $collectionsUpdaterService = $this->createMock(CollectionsUpdaterService::class);
        $collectionsUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $collectionsUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(
                new Report(
                    [
                        new Statistic('c', 3),
                    ],
                )
            )
        ;

        return new GamesCollectionsAndDexUpdaterService(
            $gamesUpdaterService,
            $dexUpdaterService,
            $collectionsUpdaterService,
        );
    }
}
