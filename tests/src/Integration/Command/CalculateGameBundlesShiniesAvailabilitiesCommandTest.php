<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\CalculateGameBundlesShiniesAvailabilitiesActionStarter;
use App\Command\AbstractCalculateCommand;
use App\Command\CalculateGameBundlesShiniesAvailabilitiesCommand;
use App\Message\AbstractActionMessage;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\MessageHandler\CalculateGameBundlesShiniesAvailabilitiesHandler;
use App\Repository\GamesShiniesAvailabilitiesRepository;
use App\Service\CalculatorService\GameBundlesShiniesAvailabilitiesCalculatorService;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleShinyAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;

/**
 * @internal
 */
#[CoversClass(CalculateGameBundlesShiniesAvailabilitiesCommand::class)]
#[CoversClass(GameBundlesShiniesAvailabilitiesCalculatorService::class)]
#[CoversClass(AbstractCalculateCommand::class)]
#[CoversClass(CalculateGameBundlesShiniesAvailabilitiesActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(CalculateGameBundlesShiniesAvailabilities::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversClass(CalculateGameBundlesShiniesAvailabilitiesHandler::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class CalculateGameBundlesShiniesAvailabilitiesCommandTest extends AbstractTestCaseCommand
{
    use CountGameShinyAvailabilityTrait;
    use CountGameBundleShinyAvailabilityTrait;
    use CountActionLogTrait;

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
            "9 bundles' shinies' availabilities calculated",
            $commandTester->getDisplay()
        );
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_shinies_availabilities';
    }
}
