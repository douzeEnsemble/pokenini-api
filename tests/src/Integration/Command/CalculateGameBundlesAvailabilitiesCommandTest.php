<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\CalculateGameBundlesAvailabilitiesActionStarter;
use App\Command\AbstractCalculateCommand;
use App\Command\CalculateGameBundlesAvailabilitiesCommand;
use App\Message\AbstractActionMessage;
use App\Message\CalculateGameBundlesAvailabilities;
use App\Repository\GamesAvailabilitiesRepository;
use App\Service\CalculatorService\GameBundlesAvailabilitiesCalculatorService;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameAvailabilityTrait;
use App\Tests\Common\Traits\CounterTrait\CountGameBundleAvailabilityTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;

/**
 * @internal
 */
#[CoversClass(CalculateGameBundlesAvailabilitiesCommand::class)]
#[CoversClass(GameBundlesAvailabilitiesCalculatorService::class)]
#[CoversClass(AbstractCalculateCommand::class)]
#[CoversClass(CalculateGameBundlesAvailabilitiesActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(CalculateGameBundlesAvailabilities::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class CalculateGameBundlesAvailabilitiesCommandTest extends AbstractTestCaseCommand
{
    use CountGameAvailabilityTrait;
    use CountGameBundleAvailabilityTrait;
    use CountActionLogTrait;

    public function testNoGamesAvailabilities(): void
    {
        /** @var GamesAvailabilitiesRepository $repo */
        $repo = self::getContainer()->get(GamesAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameAvailabilityCount());

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString("0 bundles' availabilities calculated", $commandTester->getDisplay());
    }

    public function testCalculateBundlesAvailabilities(): void
    {
        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertStringContainsString("11 bundles' availabilities calculated", $commandTester->getDisplay());
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:calculate:game_bundles_availabilities';
    }
}
