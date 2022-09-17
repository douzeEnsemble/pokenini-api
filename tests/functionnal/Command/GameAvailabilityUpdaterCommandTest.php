<?php

namespace App\Tests\Functionnal\Command;

use App\Tests\Resources\Traits\CounterTrait\CountGameAvailabilityTrait;

class GameAvailabilityUpdaterCommandTest extends AbstractUpdaterCommandTest
{
    use CountGameAvailabilityTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(34596, $this->getGameAvailabilityCount());
    }

    protected function getCommandName(): string
    {
        return 'app:update:game_availability';
    }
}
