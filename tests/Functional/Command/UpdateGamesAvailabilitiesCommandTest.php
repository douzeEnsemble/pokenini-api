<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;

class UpdateGamesAvailabilitiesCommandTest extends AbstractUpdaterCommandTest
{
    use CountGameAvailabilityTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $this->assertEquals(9, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(7980, $this->getGameAvailabilityCount());

        $this->assertEquals(9, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("7980 games' availabilities updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:games_availabilities';
    }
}
