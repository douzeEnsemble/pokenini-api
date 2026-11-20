<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\UpdatePokemonsActionStarter;
use App\Command\AbstractUpdateCommand;
use App\Command\UpdatePokemonsCommand;
use App\Message\AbstractActionMessage;
use App\Message\UpdatePokemons;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(UpdatePokemonsCommand::class)]
#[CoversClass(AbstractUpdateCommand::class)]
#[CoversClass(UpdatePokemonsActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(UpdatePokemons::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdatePokemonsCommandTest extends AbstractTestCaseCommand
{
    use CounterTableTrait;
    use CountActionLogTrait;

    #[Test]
    public function updateWithInvalidSheetFailsAndLogsError(): void
    {
        $initialErrorCount = $this->getActionLogErrorCount();

        $commandTester = $this->executeCommand(['--sheet-name' => 'wrong_sheet']);

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertSame($initialErrorCount + 1, $this->getActionLogErrorCount());
        $this->assertStringContainsString('This is not a valid data spreadsheet', $commandTester->getDisplay());
    }

    #[Test]
    public function update(): void
    {
        $this->assertSame(26, $this->getTableCount('pokemon'));

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();
        $this->assertSame(1818, $this->getTableCount('pokemon'));

        $this->assertSame($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertSame($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString('1817 pokémons updated', $commandTester->getDisplay());
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:pokemons';
    }
}
