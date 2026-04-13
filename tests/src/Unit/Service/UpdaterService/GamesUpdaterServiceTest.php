<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GamesUpdaterService;
use App\Updater\GameBundlesUpdater;
use App\Updater\GameGenerationsUpdater;
use App\Updater\GamesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesUpdaterService::class)]
final class GamesUpdaterServiceTest extends TestCase
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

        $this->assertSame('gg', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
    }

    private function getService(): GamesUpdaterService
    {
        $gameGenerationsUpdater = $this->createMock(GameGenerationsUpdater::class);
        $gameGenerationsUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gameGenerationsUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('gg'))
        ;

        $gameBundlesUpdater = $this->createMock(GameBundlesUpdater::class);
        $gameBundlesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gameBundlesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('gb'))
        ;

        $gamesUpdater = $this->createMock(GamesUpdater::class);
        $gamesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gamesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('g'))
        ;

        return new GamesUpdaterService(
            $gameGenerationsUpdater,
            $gameBundlesUpdater,
            $gamesUpdater
        );
    }
}
