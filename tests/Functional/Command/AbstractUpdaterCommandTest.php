<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractUpdaterCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    abstract protected function getCommandName(): string;

    public function setUp(): void
    {
        self::bootKernel();
    }

    protected function getCommand(): Command
    {
        $application = new Application(self::$kernel);

        return $application->find($this->getCommandName());
    }

    /**
     * @param string[] $input
     */
    protected function executeCommand(array $input = []): CommandTester
    {
        $command = $this->getCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        return $commandTester;
    }
}
