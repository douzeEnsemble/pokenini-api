<?php

namespace App\Tests\Unit\Service\UpdaterService;

use App\Service\UpdaterService\GamesUpdaterService;
use App\Updater\GameBundleUpdater;
use App\Updater\GameGenerationUpdater;
use App\Updater\GameUpdater;
use PHPUnit\Framework\TestCase;

class GamesUpdaterServiceTest extends TestCase
{
    public function testExecute(): void
    {
        $gameGenerationUpdater = $this->createMock(GameGenerationUpdater::class);
        $gameGenerationUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gameBundleUpdater = $this->createMock(GameBundleUpdater::class);
        $gameBundleUpdater
            ->expects($this->once())
            ->method('execute')
        ;
        $gameUpdater = $this->createMock(GameUpdater::class);
        $gameUpdater
            ->expects($this->once())
            ->method('execute')
        ;

        $service = new GamesUpdaterService(
            $gameGenerationUpdater,
            $gameBundleUpdater,
            $gameUpdater
        );

        $service->execute();
    }
}
