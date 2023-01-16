<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use App\Updater\RegionalDexNumbersUpdater;
use PHPUnit\Framework\TestCase;
use App\DTO\DataChangeReport\Statistic;

class RegionalDexNumbersUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
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

        $service = new RegionalDexNumbersUpdaterService(
            $regionalDexNumbersUpdater
        );

        $service->execute();
    }
}
