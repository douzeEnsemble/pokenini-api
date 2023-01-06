<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GamesUpdaterService;
use App\Updater\GameBundlesUpdater;
use App\Updater\GameGenerationsUpdater;
use App\Updater\GamesUpdater;
use PHPUnit\Framework\TestCase;

class GamesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
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

        $service = new GamesUpdaterService(
            $gameGenerationsUpdater,
            $gameBundlesUpdater,
            $gamesUpdater
        );

        $service->execute();
    }
}
