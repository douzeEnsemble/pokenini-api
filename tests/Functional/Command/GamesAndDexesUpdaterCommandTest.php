<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;

class GamesAndDexesUpdaterCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(8, $this->getTableCount('game_generation'));
        $this->assertEquals(16, $this->getTableCount('game_bundle'));
        $this->assertEquals(36, $this->getTableCount('game'));
        $this->assertEquals(6, $this->getTableCount('dex'));

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(17, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(22, $this->getTableCount('dex'));

        $this->assertStringContainsString("Games and dexes updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:games_and_dexes';
    }
}
