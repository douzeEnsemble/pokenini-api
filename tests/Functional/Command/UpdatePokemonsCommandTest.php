<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;

class UpdatePokemonsCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;
    use CountActionLogTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(19, $this->getTableCount('pokemon'));

        $this->assertEquals(9, $this->getActionLogToProcessCount());
        $this->assertEquals(5, $this->getActionLogDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();
        $this->assertEquals(1816, $this->getTableCount('pokemon'));

        $this->assertEquals(9, $this->getActionLogToProcessCount());
        $this->assertEquals(6, $this->getActionLogDoneCount());

        $this->assertStringContainsString("1815 pokémons updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:pokemons';
    }
}
