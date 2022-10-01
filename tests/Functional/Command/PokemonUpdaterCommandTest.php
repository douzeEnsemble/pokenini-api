<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;

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

        $this->assertStringContainsString("1815 pokémons updated", $commandTester->getDisplay());
    }

    protected function getCommandName(): string
    {
        return 'app:update:pokemon';
    }
}
