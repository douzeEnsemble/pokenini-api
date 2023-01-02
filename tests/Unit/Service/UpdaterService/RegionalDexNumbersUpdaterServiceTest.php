<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Updater\RegionalDexNumberUpdater;
use PHPUnit\Framework\TestCase;
use App\DTO\DataChangeReport\Statistic;

class RegionalDexNumbersUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $regionalDexNumberUpdater = $this->createMock(RegionalDexNumberUpdater::class);
        $regionalDexNumberUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionalDexNumberUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('rdn'))
        ;

        $service = new RegionalDexNumbersUpdaterService(
            $regionalDexNumberUpdater
        );

        $service->execute();
    }
}
