<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\UpdaterService;

use App\DTO\DataChangeReport\Statistic;
use App\Service\UpdaterService\GamesShiniesAvailabilitiesUpdaterService;
use App\Updater\GamesShiniesAvailabilitiesUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GamesShiniesAvailabilitiesUpdaterService::class)]
final class GamesShiniesAvailabilitiesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $service = $this->getService();

        $service->execute();
    }

    public function testGetReport(): void
    {
        $service = $this->getService();

        $service->execute();
        $service->getReport();
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
