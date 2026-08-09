<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\FormsUpdaterService;
use App\Updater\Forms\CategoryFormsUpdater;
use App\Updater\Forms\RegionalFormsUpdater;
use App\Updater\Forms\SpecialFormsUpdater;
use App\Updater\Forms\VariantFormsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormsUpdaterService::class)]
final class FormsUpdaterServiceTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $service = $this->getService();

        $service->execute();
    }

    #[Test]
    public function getReport(): void
    {
        $service = $this->getService();

        $service->execute();
        $report = $service->getReport();

        $this->assertSame('cat', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
    }

    private function getService(): FormsUpdaterService
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

        return new FormsUpdaterService(
            $categoryFormUpdater,
            $regionalFormUpdater,
            $specialFormUpdater,
            $variantFormUpdater
        );
    }
}
