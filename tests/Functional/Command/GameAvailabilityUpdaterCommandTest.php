<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;

class GameAvailabilityUpdaterCommandTest extends AbstractUpdaterCommandTest
{
    use CountGameAvailabilityTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(7560, $this->getGameAvailabilityCount());
    }

    protected function getCommandName(): string
    {
        return 'app:update:game_availability';
    }
}
