<?php

namespace App\Tests\Functionnal\\Command;

use App\Tests\resources\functionnal\CounterTrait\CountGameAvailabilityTrait;

class ImportAvailabilityCommandTest extends AbstractImportFileCommandTest
{
    use CountGameAvailabilityTrait;

    public function testFileNoDataCsv(): void
    {
        $commandTester = $this->executeCommand(['file' => 'tests/resources/data/game_availability_list/zero_data.csv']);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('No data to import', $commandTester->getDisplay());
    }

    public function testImportGameAvailabilities(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        $commandTester = $this->executeCommand(['file' => 'tests/resources/data/game_availability_list/data.csv']);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("108 games' availabilities created", $commandTester->getDisplay());

        $this->assertEquals(108, $this->getGameAvailabilityCount());
    }

    protected function getCommandName(): string
    {
        return 'app:import:game_availability';
    }
}
