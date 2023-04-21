<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;

class UpdateGamesAndDexCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(19, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(7, $this->getTableCount('dex'));

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(19, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(23, $this->getTableCount('dex'));

        $this->assertEquals(8, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("9 game's generations updated", $commandTester->getDisplay());
        $this->assertStringContainsString("18 game's bundles updated", $commandTester->getDisplay());
        $this->assertStringContainsString("36 games updated", $commandTester->getDisplay());
        $this->assertStringContainsString("21 dex updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:games_and_dex';
    }
}
