<?php

declare(strict_types=1);

namespace App\Tests\Functional\Updater;

use App\Tests\Resources\Traits\CounterTrait\CountPokemonTrait;
use App\Tests\Resources\Traits\GetterTrait\GetPokemonTrait;
use App\Updater\AbstractUpdater;
use App\Updater\PokemonUpdater;

class PokemonUpdaterTest extends AbstractUpdaterTest
{
    use CountPokemonTrait;
    use GetPokemonTrait;

    protected int $initialTotalCount = 16;
    protected int $finalTotalCount = 1816;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Pokémons';
    protected string $tableName = 'pokemon';

    public function testImportNewPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $this->getService()->execute('pokemon_list / only_new');

        $this->assertEquals(27, $this->getPokemonCount());
        $this->assertEquals(11, $this->getPokemonNotDeletedCount());
        $this->assertEquals(16, $this->getPokemonDeletedCount());

        $charmander = $this->getPokemonFromName('Charmander');
        $charmeleon = $this->getPokemonFromName('Charmeleon');

        $this->assertNull($charmander['family_id']);
        $this->assertNotNull($charmeleon['family_id']);
        $this->assertEquals($charmander['id'], $charmeleon['family_id']);

        $this->assertNotNull($charmander['category_form_id']);
        $this->assertNull($charmeleon['category_form_id']);
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

        $this->assertNotNull($bulbasaurBefore['category_form_id']);
        $this->assertNull($ivysaurBefore['category_form_id']);

        $this->getService()->execute('pokemon_list / only_existing');

        $this->assertEquals(16, $this->getPokemonCount());
        $this->assertEquals(7, $this->getPokemonNotDeletedCount());
        $this->assertEquals(9, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');

        $this->assertFalse($pokemonAfter['bankable']);
        $this->assertFalse($pokemonAfter['bankableish']);

        $bulbasaurAfter = $this->getPokemonFromName('Bulbasaur');
        $ivysaurAfter = $this->getPokemonFromName('Ivysaur');

        $this->assertNull($bulbasaurAfter['family_id']);
        $this->assertNotNull($ivysaurAfter['family_id']);
        $this->assertEquals($bulbasaurAfter['id'], $ivysaurAfter['family_id']);

        $this->assertNotNull($bulbasaurAfter['category_form_id']);
        $this->assertNull($ivysaurAfter['category_form_id']);
    }

    public function testImportNewAndExistingPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $this->getService()->execute('pokemon_list / new_and_existing');

        $this->assertEquals(27, $this->getPokemonCount());
        $this->assertEquals(17, $this->getPokemonNotDeletedCount());
        $this->assertEquals(10, $this->getPokemonDeletedCount());
    }

    public function testUpdateRegionalFormPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $pokemonBefore = $this->getPokemonFromName('Douze');
        $this->assertNull($pokemonBefore['regional_form_id']);

        $this->getService()->execute('pokemon_list / update_regional_form');

        $this->assertEquals(16, $this->getPokemonCount());
        $this->assertEquals(1, $this->getPokemonNotDeletedCount());
        $this->assertEquals(15, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');
        $this->assertNotNull($pokemonAfter['regional_form_id']);
    }

    public function testDifferentColumnsOrderPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $this->getService()->execute('pokemon_list / different_columns_order');

        $this->assertEquals(27, $this->getPokemonCount());
        $this->assertEquals(17, $this->getPokemonNotDeletedCount());
        $this->assertEquals(10, $this->getPokemonDeletedCount());
    }

    protected function getService(): AbstractUpdater
    {
        /** @var PokemonUpdater */
        return static::getContainer()->get(PokemonUpdater::class);
    }
}
