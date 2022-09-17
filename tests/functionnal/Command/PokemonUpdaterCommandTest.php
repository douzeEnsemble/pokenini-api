<?php

namespace App\Tests\Functionnal\Command;

use App\Tests\Resources\Traits\CounterTrait\CountPokemonTrait;

class PokemonUpdaterCommandTest extends AbstractUpdaterCommandTest
{
    use CountPokemonTrait;

    public function testUpdate(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $commandTester = $this->executeCommand();

        $commandTester->assertCommandIsSuccessful();

        $this->assertEquals(1816, $this->getPokemonCount());
        $this->assertEquals(1815, $this->getPokemonNotDeletedCount());
        $this->assertEquals(1, $this->getPokemonDeletedCount());
    }

    protected function getCommandName(): string
    {
        return 'app:update:pokemon';
    }
}
