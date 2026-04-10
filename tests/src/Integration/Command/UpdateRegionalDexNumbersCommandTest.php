<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\UpdateRegionalDexNumbersActionStarter;
use App\Command\AbstractUpdateCommand;
use App\Command\UpdateRegionalDexNumbersCommand;
use App\Message\AbstractActionMessage;
use App\Message\UpdateRegionalDexNumbers;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountRegionalDexNumberTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;

/**
 * @internal
 */
#[CoversClass(UpdateRegionalDexNumbersCommand::class)]
#[CoversClass(AbstractUpdateCommand::class)]
#[CoversClass(UpdateRegionalDexNumbersActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(UpdateRegionalDexNumbers::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateRegionalDexNumbersCommandTest extends AbstractTestCaseCommand
{
    use CountRegionalDexNumberTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getRegionalDexNumberCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(4419, $this->getRegionalDexNumberCount());

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString('4419 regional dex numbers updated', $commandTester->getDisplay());
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:regional_dex_numbers';
    }
}
