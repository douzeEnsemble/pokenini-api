<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Report;
use App\Service\UpdaterService\DexUpdaterService;
use App\Service\UpdaterService\GamesAndDexUpdaterService;
use App\Service\UpdaterService\GamesUpdaterService;
use PHPUnit\Framework\TestCase;

class GamesAndDexUpdaterServiceTest extends TestCase
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

        $dexUpdaterService = $this->createMock(DexUpdaterService::class);
        $dexUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $dexUpdaterService
            ->expects($this->once())
            ->method('getReport')
            ->willReturn(new Report([]))
        ;

        $service = new GamesAndDexUpdaterService(
            $gamesUpdaterService,
            $dexUpdaterService
        );

        $service->execute();
    }
}
