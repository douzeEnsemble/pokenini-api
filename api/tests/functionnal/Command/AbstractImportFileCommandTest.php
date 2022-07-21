<?php

namespace App\Tests\Functionnal\Command;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractImportFileCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    abstract protected function getCommandName(): string;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testFileNotSet(): void
    {
        $this->expectException(RuntimeException::class);

        $this->executeCommand([]);
    }

    public function testFileNotExists(): void
    {
        $commandTester = $this->executeCommand(['file' => 'spoon.neo']);

        $this->assertEquals(2, $commandTester->getStatusCode());

        $this->assertStringContainsString('File not found', $commandTester->getDisplay());
    }

    public function testFileNotCsv(): void
    {
        $commandTester = $this->executeCommand(['file' => 'tests/resources/data/not_csv.jpg']);

        $this->assertEquals(2, $commandTester->getStatusCode());

        $this->assertStringContainsString(
            'File is not a valid csv (image/jpeg isn\'t accepted)',
            $commandTester->getDisplay()
        );
    }

    public function testFileNotAGoodCsv(): void
    {
        $commandTester = $this->executeCommand(['file' => 'tests/resources/data/just_a.csv']);

        $this->assertEquals(2, $commandTester->getStatusCode());

        $this->assertStringContainsString('This is not a valid data csv file', $commandTester->getDisplay());
    }

    public function testFileEmpty(): void
    {
        $commandTester = $this->executeCommand(['file' => 'tests/resources/data/empty.csv']);

        $this->assertEquals(2, $commandTester->getStatusCode());

        $this->assertStringContainsString(
            'File is not a valid csv (application/x-empty isn\'t accepted)',
            $commandTester->getDisplay()
        );
    }

    protected function getCommand(): Command
    {
        $application = new Application(self::$kernel);

        return $application->find($this->getCommandName());
    }

    /**
     * @param string[] $input
     */
    protected function executeCommand(array $input): CommandTester
    {
        $command = $this->getCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        return $commandTester;
    }
}
