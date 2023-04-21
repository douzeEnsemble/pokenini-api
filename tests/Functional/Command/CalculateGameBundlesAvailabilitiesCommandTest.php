<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\GamesAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateGameBundlesAvailabilitiesCommandTest extends KernelTestCase
{
    use CountGameAvailabilityTrait;
    use CountGameBundleAvailabilityTrait;
    use RefreshDatabaseTrait;
    use CountActionLogTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testNoGamesAvailabilities(): void
    {
        /** @var GamesAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(GamesAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameAvailabilityCount());

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("0 bundles' availabilities calculated", $commandTester->getDisplay());
    }

    public function testCalculateBundlesAvailabilities(): void
    {
        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("18 bundles' availabilities calculated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_availabilities';
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
