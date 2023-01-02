<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\DexesUpdaterService;
use App\Updater\DexUpdater;
use PHPUnit\Framework\TestCase;

class DexesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
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

        $service = new DexesUpdaterService(
            $dexUpdater,
        );

        $service->execute();
    }
}
