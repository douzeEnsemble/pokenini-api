<?php

namespace App\Tests\Unit\Service\UpdaterService;

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

        $service = new DexesUpdaterService(
            $dexUpdater,
        );

        $service->execute();
    }
}
