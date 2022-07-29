<?php

namespace App\Tests\Functionnal\Command;

use App\Tests\Resources\functionnal\CounterTrait\CountPokemonTrait;

class ImportPokemonCommandTest extends AbstractImportFileCommandTest
{
    use CountPokemonTrait;

    public function testFileNoDataCsv(): void
    {
        $commandTester = $this->executeCommand([
            'file' => 'tests/resources/data/pokemon_list/zero_data.csv'
        ]);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('No data to import', $commandTester->getDisplay());
    }

    public function testImportNewPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $commandTester = $this->executeCommand([
            'file' => 'tests/resources/data/pokemon_list/only_new.csv'
        ]);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('11 pokemons created/updated', $commandTester->getDisplay());

        $this->assertEquals(22, $this->getPokemonCount());
        $this->assertEquals(11, $this->getPokemonNotDeletedCount());
        $this->assertEquals(11, $this->getPokemonDeletedCount());

        $charmander = $this->getPokemonFromName('Charmander');
        $charmeleon = $this->getPokemonFromName('Charmeleon');

        $this->assertNull($charmander['family_id']);
        $this->assertNotNull($charmeleon['family_id']);
        $this->assertEquals($charmander['id'], $charmeleon['family_id']);
    }

    public function testImportExistingPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $pokemonBefore = $this->getPokemonFromName('Douze');

        $this->assertTrue($pokemonBefore['bankable']);
        $this->assertNull($pokemonBefore['bankableish']);

        $bulbasaurBefore = $this->getPokemonFromName('Bulbasaur');
        $ivysaurBefore = $this->getPokemonFromName('Ivysaur');

        $this->assertNull($bulbasaurBefore['family_id']);
        $this->assertNotNull($ivysaurBefore['family_id']);
        $this->assertEquals($bulbasaurBefore['id'], $ivysaurBefore['family_id']);

        $commandTester = $this->executeCommand([
            'file' => 'tests/resources/data/pokemon_list/only_existing.csv'
        ]);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('7 pokemons created/updated', $commandTester->getDisplay());

        $this->assertEquals(11, $this->getPokemonCount());
        $this->assertEquals(7, $this->getPokemonNotDeletedCount());
        $this->assertEquals(4, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');

        $this->assertFalse($pokemonAfter['bankable']);
        $this->assertFalse($pokemonAfter['bankableish']);

        $bulbasaurAfter = $this->getPokemonFromName('Bulbasaur');
        $ivysaurAfter = $this->getPokemonFromName('Ivysaur');

        $this->assertNull($bulbasaurAfter['family_id']);
        $this->assertNotNull($ivysaurAfter['family_id']);
        $this->assertEquals($bulbasaurAfter['id'], $ivysaurAfter['family_id']);
    }

    public function testImportNewAndExistingPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $commandTester = $this->executeCommand([
            'file' => 'tests/resources/data/pokemon_list/new_and_existing.csv'
        ]);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('17 pokemons created/updated', $commandTester->getDisplay());

        $this->assertEquals(22, $this->getPokemonCount());
        $this->assertEquals(17, $this->getPokemonNotDeletedCount());
        $this->assertEquals(5, $this->getPokemonDeletedCount());
    }

    public function testUpdateRegionalFormPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $pokemonBefore = $this->getPokemonFromName('Douze');
        $this->assertNull($pokemonBefore['regional_form_id']);

        $commandTester = $this->executeCommand([
            'file' => 'tests/resources/data/pokemon_list/update_regional_form.csv'
        ]);

        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('1 pokemons created/updated', $commandTester->getDisplay());

        $this->assertEquals(11, $this->getPokemonCount());
        $this->assertEquals(1, $this->getPokemonNotDeletedCount());
        $this->assertEquals(10, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');
        $this->assertNotNull($pokemonAfter['regional_form_id']);
    }

    protected function getCommandName(): string
    {
        return 'app:import:pokemon';
    }
}
