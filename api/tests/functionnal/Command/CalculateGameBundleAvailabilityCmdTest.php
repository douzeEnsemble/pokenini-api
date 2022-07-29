<?php

namespace App\Tests\Functionnal\Command;

use App\Repository\GameAvailabilityRepository;
use App\Tests\Resources\functionnal\CounterTrait\CountGameAvailabilityTrait;
use App\Tests\Resources\functionnal\CounterTrait\CountGameBundleAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\Cache\CacheInterface;

class CalculateGameBundleAvailabilityCmdTest extends KernelTestCase
{
    use CountGameAvailabilityTrait;
    use CountGameBundleAvailabilityTrait;
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testNoGameAvailability(): void
    {
        /** @var GameAvailabilityRepository $repo */
        $repo = static::getContainer()->get(GameAvailabilityRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameAvailabilityCount());

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("0 bundles' availabilities calculated", $commandTester->getDisplay());
    }

    public function testCalculateBundlesAvailabilities(): void
    {
        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString("18 bundles' availabilities calculated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundle_availability';
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
