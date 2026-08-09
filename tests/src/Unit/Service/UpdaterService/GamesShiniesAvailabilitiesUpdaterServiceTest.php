<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GamesShiniesAvailabilitiesUpdaterService;
use App\Updater\GamesShiniesAvailabilitiesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesShiniesAvailabilitiesUpdaterService::class)]
final class GamesShiniesAvailabilitiesUpdaterServiceTest extends TestCase
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

        $this->assertSame('ga', $report->detail[0]->slug);
        $this->assertSame(0, $report->detail[0]->count);
    }

    private function getService(): GamesShiniesAvailabilitiesUpdaterService
    {
        $gamesShiniesAvailabilitiesUpdater = $this->createMock(GamesShiniesAvailabilitiesUpdater::class);
        $gamesShiniesAvailabilitiesUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gamesShiniesAvailabilitiesUpdater
            ->expects($this->once())
            ->method('getStatistic')
            ->willReturn(new Statistic('ga'))
        ;

        return new GamesShiniesAvailabilitiesUpdaterService(
            $gamesShiniesAvailabilitiesUpdater
        );
    }
}
