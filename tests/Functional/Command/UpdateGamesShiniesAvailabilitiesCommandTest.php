<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;

class UpdateGamesShiniesAvailabilitiesCommandTest extends AbstractUpdaterCommandTest
{
    use CountGameShinyAvailabilityTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getGameShinyAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(2622, $this->getGameShinyAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("2622 games' shinies' availabilities updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:games_shinies_availabilities';
    }
}
