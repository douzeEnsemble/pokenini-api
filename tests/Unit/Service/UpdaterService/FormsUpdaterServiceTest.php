<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\FormsUpdaterService;
use App\Updater\Forms\CategoryFormsUpdater;
use App\Updater\Forms\RegionalFormsUpdater;
use App\Updater\Forms\SpecialFormsUpdater;
use App\Updater\Forms\VariantFormsUpdater;
use PHPUnit\Framework\TestCase;

class FormsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $categoryFormUpdater = $this->createMock(CategoryFormsUpdater::class);
        $categoryFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $categoryFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('cat'))
        ;

        $regionalFormUpdater = $this->createMock(RegionalFormsUpdater::class);
        $regionalFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionalFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('region'))
        ;

        $specialFormUpdater = $this->createMock(SpecialFormsUpdater::class);
        $specialFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $specialFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('spec'))
        ;

        $variantFormUpdater = $this->createMock(VariantFormsUpdater::class);
        $variantFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $variantFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('var'))
        ;

        $service = new FormsUpdaterService(
            $categoryFormUpdater,
            $regionalFormUpdater,
            $specialFormUpdater,
            $variantFormUpdater
        );

        $service->execute();
    }
}
