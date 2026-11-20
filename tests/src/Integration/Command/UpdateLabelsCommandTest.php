<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\UpdateLabelsActionStarter;
use App\Command\AbstractUpdateCommand;
use App\Command\UpdateLabelsCommand;
use App\Message\AbstractActionMessage;
use App\Message\UpdateLabels;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(UpdateLabelsCommand::class)]
#[CoversClass(AbstractUpdateCommand::class)]
#[CoversClass(UpdateLabelsActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(UpdateLabels::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateLabelsCommandTest extends AbstractTestCaseCommand
{
    use CounterTableTrait;
    use CountActionLogTrait;

    #[Test]
    public function update(): void
    {
        $this->assertSame(5, $this->getTableCount('catch_state'));
        $this->assertSame(3, $this->getTableCount('dex_category'));
        $this->assertSame(3, $this->getTableCount('category_form'));
        $this->assertSame(3, $this->getTableCount('regional_form'));
        $this->assertSame(4, $this->getTableCount('special_form'));
        $this->assertSame(7, $this->getTableCount('variant_form'));
        $this->assertSame(10, $this->getTableCount('region'));
        $this->assertSame(19, $this->getTableCount('type'));

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertSame(9, $this->getTableCount('catch_state'));
        $this->assertSame(4, $this->getTableCount('dex_category'));
        $this->assertSame(4, $this->getTableCount('category_form'));
        $this->assertSame(4, $this->getTableCount('regional_form'));
        $this->assertSame(5, $this->getTableCount('special_form'));
        $this->assertSame(8, $this->getTableCount('variant_form'));
        $this->assertSame(10, $this->getTableCount('region'));
        $this->assertSame(20, $this->getTableCount('type'));

        $this->assertSame($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertSame($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString("6 catch's states updated", $commandTester->getDisplay());
        $this->assertStringContainsString('3 dex categories updated', $commandTester->getDisplay());
        $this->assertStringContainsString('4 category forms updated', $commandTester->getDisplay());
        $this->assertStringContainsString('4 regional forms updated', $commandTester->getDisplay());
        $this->assertStringContainsString('5 special forms updated', $commandTester->getDisplay());
        $this->assertStringContainsString('8 variant forms updated', $commandTester->getDisplay());
        $this->assertStringContainsString('10 regions updated', $commandTester->getDisplay());
        $this->assertStringContainsString('18 types updated', $commandTester->getDisplay());
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:labels';
    }
}
