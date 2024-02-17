<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\GamesShiniesAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleShinyAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;

class CalculateGameBundlesShiniesAvailabilitiesCommandTest extends AbstractTestCaseCommand
{
    use CountGameShinyAvailabilityTrait;
    use CountGameBundleShinyAvailabilityTrait;
    use CountActionLogTrait;

    public function testNoGamesShiniesAvailabilities(): void
    {
        /** @var GamesShiniesAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(GamesShiniesAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameShinyAvailabilityCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString(
            "0 bundles' shinies' availabilities calculated",
            $commandTester->getDisplay()
        );
    }

    public function testCalculateBundlesShiniesAvailabilities(): void
    {
        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString(
            "9 bundles' shinies' availabilities calculated",
            $commandTester->getDisplay()
        );
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_shinies_availabilities';
    }
}
