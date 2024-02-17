<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountDexAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
use App\Tests\Common\Traits\HasserTrait\HasDexAvailabilityTrait;

class CalculateDexAvailabilitiesCommandTest extends AbstractTestCaseCommand
{
    use CountPokemonTrait;
    use CountDexAvailabilityTrait;
    use HasDexAvailabilityTrait;
    use CountActionLogTrait;

    public function testNoDexAvailabilities(): void
    {
        /** @var PokemonsRepository $repo */
        $repo = static::getContainer()->get(PokemonsRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getPokemonNotDeletedCount());

        $this->assertEquals(49, $this->getDexAvailabilityCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("0 dex' availabilities calculated", $commandTester->getDisplay());

        $this->assertEquals(0, $this->getDexAvailabilityCount());

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());
    }

    public function testDexAvailabilities(): void
    {
        $this->assertEquals(49, $this->getDexAvailabilityCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("77 dex' availabilities calculated", $commandTester->getDisplay());

        $this->assertEquals(77, $this->getDexAvailabilityCount());

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertTrue($this->hasDexAvailability('Red / Green / Blue / Yellow', 'bulbasaur'));
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:dex_availabilities';
    }
}
