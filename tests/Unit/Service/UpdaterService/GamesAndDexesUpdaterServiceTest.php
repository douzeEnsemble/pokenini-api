<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\DexesUpdaterService;
use App\Service\UpdaterService\GamesAndDexesUpdaterService;
use App\Service\UpdaterService\GamesUpdaterService;
use PHPUnit\Framework\TestCase;

class GamesAndDexesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $gamesUpdaterService = $this->createMock(GamesUpdaterService::class);
        $gamesUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $gamesUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(new Report([]))
        ;

        $dexesUpdaterService = $this->createMock(DexesUpdaterService::class);
        $dexesUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $dexesUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(new Report([]))
        ;

        $service = new GamesAndDexesUpdaterService(
            $gamesUpdaterService,
            $dexesUpdaterService
        );

        $service->execute();
    }
}
