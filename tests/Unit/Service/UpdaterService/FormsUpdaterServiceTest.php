<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\FormsUpdaterService;
use App\Updater\Form\CategoryFormUpdater;
use App\Updater\Form\RegionalFormUpdater;
use App\Updater\Form\SpecialFormUpdater;
use App\Updater\Form\VariantFormUpdater;
use PHPUnit\Framework\TestCase;

class FormsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $categoryFormUpdater = $this->createMock(CategoryFormUpdater::class);
        $categoryFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $categoryFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('cat'))
        ;

        $regionalFormUpdater = $this->createMock(RegionalFormUpdater::class);
        $regionalFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionalFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('region'))
        ;

        $specialFormUpdater = $this->createMock(SpecialFormUpdater::class);
        $specialFormUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $specialFormUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('spec'))
        ;

        $variantFormUpdater = $this->createMock(VariantFormUpdater::class);
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
