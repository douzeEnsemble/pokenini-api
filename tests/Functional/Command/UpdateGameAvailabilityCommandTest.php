<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;

class UpdateGameAvailabilityCommandTest extends AbstractUpdaterCommandTest
{
    use CountGameAvailabilityTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(7980, $this->getGameAvailabilityCount());

        $this->assertStringContainsString("7980 games' availabilities updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:game_availability';
    }
}
