<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Command;

use App\Repository\PokemonRepository;
use App\Tests\Resources\Traits\CounterTrait\CountDexAvailabilityTrait;
use App\Tests\Resources\Traits\CounterTrait\CountPokemonTrait;
use App\Tests\Resources\Traits\HasserTrait\HasDexAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateDexAvailabilityCommandTest extends KernelTestCase
{
    use CountPokemonTrait;
    use CountDexAvailabilityTrait;
    use HasDexAvailabilityTrait;
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testNoGameAvailability(): void
    {
        /** @var PokemonRepository $repo */
        $repo = static::getContainer()->get(PokemonRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getPokemonNotDeletedCount());

        $this->assertEquals(36, $this->getDexAvailabilityCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("0 dex' availabilities calculated", $commandTester->getDisplay());

        $this->assertEquals(0, $this->getDexAvailabilityCount());
    }

    public function testDexAvailabilities(): void
    {
        $this->assertEquals(36, $this->getDexAvailabilityCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("53 dex' availabilities calculated", $commandTester->getDisplay());

        $this->assertEquals(53, $this->getDexAvailabilityCount());

        $this->assertTrue($this->hasDexAvailability('Red / Green / Blue / Yellow', 'Bulbasaur'));
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:dex_availability';
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
