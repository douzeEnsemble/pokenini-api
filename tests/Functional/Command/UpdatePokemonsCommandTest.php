<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use App\Tests\Common\Traits\CounterTrait\CountMessengerActionTrait;

class UpdatePokemonsCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;
    use CountMessengerActionTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(19, $this->getTableCount('pokemon'));

        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(5, $this->getMessengerActionDoneCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();
        $this->assertEquals(1816, $this->getTableCount('pokemon'));

        $this->assertEquals(7, $this->getMessengerActionToProcessCount());
        $this->assertEquals(6, $this->getMessengerActionDoneCount());

        $this->assertStringContainsString("1815 pokémons updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:pokemons';
    }
}
