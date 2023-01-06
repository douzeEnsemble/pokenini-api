<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GamesAvailabilitiesUpdaterService;
use App\Updater\GamesAvailabilitiesUpdater;
use PHPUnit\Framework\TestCase;

class GamesAvailabilitiesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $gamesAvailabilitiesUpdater = $this->createMock(GamesAvailabilitiesUpdater::class);
        $gamesAvailabilitiesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gamesAvailabilitiesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('ga'))
        ;

        $service = new GamesAvailabilitiesUpdaterService(
            $gamesAvailabilitiesUpdater
        );

        $service->execute();
    }
}
