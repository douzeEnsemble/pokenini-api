<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\CollectionsAvailabilitiesUpdaterService;
use App\Updater\CollectionsAvailabilitiesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsAvailabilitiesUpdaterService::class)]
final class CollectionsAvailabilitiesUpdaterServiceTest extends TestCase
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

        $this->assertSame('ca', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
    }

    private function getService(): CollectionsAvailabilitiesUpdaterService
    {
        $collectionsAvailabilitiesUpdater = $this->createMock(CollectionsAvailabilitiesUpdater::class);
        $collectionsAvailabilitiesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $collectionsAvailabilitiesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('ca'))
        ;

        return new CollectionsAvailabilitiesUpdaterService(
            $collectionsAvailabilitiesUpdater
        );
    }
}
