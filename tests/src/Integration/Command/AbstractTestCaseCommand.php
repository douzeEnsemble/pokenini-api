<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractTestCaseCommand extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    abstract protected function getCommandName(): string;

    protected function getCommand(): Command
    {
        if (null === self::$kernel) {
            throw new \RuntimeException('kernel is not booted, use setUp()');
        }

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
