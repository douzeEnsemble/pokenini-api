<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GameAvailabilitiesUpdaterService;
use App\Updater\GameAvailabilityUpdater;
use PHPUnit\Framework\TestCase;

class GameAvailabilitiesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $gameAvailabilityUpdater = $this->createMock(GameAvailabilityUpdater::class);
        $gameAvailabilityUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gameAvailabilityUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('ga'))
        ;

        $service = new GameAvailabilitiesUpdaterService(
            $gameAvailabilityUpdater
        );

        $service->execute();
    }
}
