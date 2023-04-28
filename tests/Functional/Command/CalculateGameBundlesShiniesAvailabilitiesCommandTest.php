<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Repository\GamesShiniesAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleShinyAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateGameBundlesShiniesAvailabilitiesCommandTest extends KernelTestCase
{
    use CountGameShinyAvailabilityTrait;
    use CountGameBundleShinyAvailabilityTrait;
    use RefreshDatabaseTrait;
    use CountActionLogTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testNoGamesShiniesAvailabilities(): void
    {
        /** @var GamesShiniesAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(GamesShiniesAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameShinyAvailabilityCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString(
            "0 bundles' shinies' availabilities calculated",
            $commandTester->getDisplay()
        );
    }

    public function testCalculateBundlesShiniesAvailabilities(): void
    {
        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString(
            "16 bundles' shinies' availabilities calculated",
            $commandTester->getDisplay()
        );
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_shinies_availabilities';
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
