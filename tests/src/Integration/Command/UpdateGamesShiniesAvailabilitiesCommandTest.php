<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\UpdateGamesShiniesAvailabilitiesActionStarter;
use App\Command\AbstractUpdateCommand;
use App\Command\UpdateGamesShiniesAvailabilitiesCommand;
use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesShiniesAvailabilities;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(UpdateGamesShiniesAvailabilitiesCommand::class)]
#[CoversClass(AbstractUpdateCommand::class)]
#[CoversClass(UpdateGamesShiniesAvailabilitiesActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(UpdateGamesShiniesAvailabilities::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateGamesShiniesAvailabilitiesCommandTest extends AbstractTestCaseCommand
{
    use CountGameShinyAvailabilityTrait;
    use CountActionLogTrait;

    #[Test]
    public function update(): void
    {
        $this->assertGreaterThan(0, $this->getGameShinyAvailabilityCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(4598, $this->getGameShinyAvailabilityCount());

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString("4598 games' shinies' availabilities updated", $commandTester->getDisplay());
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:games_shinies_availabilities';
    }
}
