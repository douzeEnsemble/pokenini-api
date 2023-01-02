<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;

class UpdatePokemonsCommandTest extends AbstractUpdaterCommandTest
{
    use CounterTableTrait;

    public function testUpdate(): void
    {
        $this->assertEquals(19, $this->getTableCount('pokemon'));

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();
        $this->assertEquals(1816, $this->getTableCount('pokemon'));

        $this->assertStringContainsString("1815 pokémons updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:pokemons';
    }
}
