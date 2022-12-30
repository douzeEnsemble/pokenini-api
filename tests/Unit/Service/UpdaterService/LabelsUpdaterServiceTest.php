<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\Service\UpdaterService\FormsUpdaterService;
use App\Service\UpdaterService\LabelsUpdaterService;
use App\Updater\CatchStateUpdater;
use App\Updater\RegionUpdater;
use PHPUnit\Framework\TestCase;

class LabelsUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $formsUpdaterService = $this->createMock(FormsUpdaterService::class);
        $formsUpdaterService
            ->expects($this->once())
            ->method('execute')
        ;
        $catchStateUpdater = $this->createMock(CatchStateUpdater::class);
        $catchStateUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $regionUpdater = $this->createMock(RegionUpdater::class);
        $regionUpdater
            ->expects($this->once())
            ->method('execute')
        ;

        $service = new LabelsUpdaterService(
            $catchStateUpdater,
            $formsUpdaterService,
            $regionUpdater
        );

        $service->execute();
    }
}
