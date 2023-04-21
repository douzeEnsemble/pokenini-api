<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountDexAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
use App\Tests\Common\Traits\HasserTrait\HasDexAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateDexAvailabilitiesCommandTest extends KernelTestCase
{
    use CountPokemonTrait;
    use CountDexAvailabilityTrait;
    use HasDexAvailabilityTrait;
    use RefreshDatabaseTrait;
    use CountActionLogTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testNoDexAvailabilities(): void
    {
        /** @var PokemonsRepository $repo */
        $repo = static::getContainer()->get(PokemonsRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getPokemonNotDeletedCount());

        $this->assertEquals(39, $this->getDexAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("0 dex' availabilities calculated", $commandTester->getDisplay());

        $this->assertEquals(0, $this->getDexAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());
    }

    public function testDexAvailabilities(): void
    {
        $this->assertEquals(39, $this->getDexAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("61 dex' availabilities calculated", $commandTester->getDisplay());

        $this->assertEquals(61, $this->getDexAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertTrue($this->hasDexAvailability('Red / Green / Blue / Yellow', 'Bulbasaur'));
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:dex_availabilities';
    }

    protected function getCommand(): Command
    {
        $application = new Application(self::$kernel);

        return $application->find($this->getCommandName());
    }

    protected function executeCommand(): CommandTester
    {
        $command = $this->getCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        return $commandTester;
    }
}
