<?php

declare(strict_types=1);

namespace App\Tests\Integration\Updater;

use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokemonTrait;
use App\Updater\AbstractUpdater;
use App\Updater\PokemonsUpdater;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PokemonsUpdater::class)]
#[CoversClass(AbstractUpdater::class)]
final class PokemonsUpdaterTest extends AbstractTestUpdater
{
    use CountPokemonTrait;
    use GetPokemonTrait;

    protected int $initialTotalCount = 26;
    protected int $finalTotalCount = 1818;
    protected int $mustBeDeletedTotalCount = 1;
    protected string $sheetName = 'Pokémons';
    protected string $tableName = 'pokemon';

    public function testImportNewPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $this->getService()->execute('pokemon_list / only_new');

        $this->assertEquals(34, $this->getPokemonCount());
        $this->assertEquals(8, $this->getPokemonNotDeletedCount());
        $this->assertEquals(26, $this->getPokemonDeletedCount());

        $charmander = $this->getPokemonFromName('Charmander');
        $charmeleon = $this->getPokemonFromName('Charmeleon');

        $this->assertEquals('charmander', $charmander['family']);
        $this->assertEquals('charmander', $charmeleon['family']);

        $this->assertNotNull($charmander['category_form_id']);
        $this->assertNull($charmeleon['category_form_id']);

        $this->assertNotNull($charmeleon['primary_type_id']);
        $this->assertNull($charmeleon['secondary_type_id']);

        $charizard = $this->getPokemonFromName('Charizard');

        $this->assertNotNull($charizard['primary_type_id']);
        $this->assertNotNull($charizard['secondary_type_id']);
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

        $this->assertEquals('bulbasaur', $bulbasaurBefore['family']);
        $this->assertEquals('bulbasaur', $ivysaurBefore['family']);

        $this->assertNotNull($bulbasaurBefore['category_form_id']);
        $this->assertNull($ivysaurBefore['category_form_id']);

        $this->assertNotNull($bulbasaurBefore['primary_type_id']);
        $this->assertNotNull($bulbasaurBefore['secondary_type_id']);

        $this->getService()->execute('pokemon_list / only_existing');

        $this->assertEquals(26, $this->getPokemonCount());
        $this->assertEquals(7, $this->getPokemonNotDeletedCount());
        $this->assertEquals(19, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');

        $this->assertFalse($pokemonAfter['bankable']);
        $this->assertFalse($pokemonAfter['bankableish']);

        $bulbasaurAfter = $this->getPokemonFromName('Bulbasaur');
        $ivysaurAfter = $this->getPokemonFromName('Ivysaur');

        $this->assertNull($bulbasaurAfter['deleted_at']);
        $this->assertNull($ivysaurAfter['deleted_at']);

        $this->assertEquals('bulbasaur', $bulbasaurAfter['family']);
        $this->assertEquals('bulbasaur', $ivysaurAfter['family']);

        $this->assertNotNull($bulbasaurAfter['category_form_id']);
        $this->assertNull($ivysaurAfter['category_form_id']);

        $this->assertNotNull($bulbasaurAfter['primary_type_id']);
        $this->assertNotNull($bulbasaurAfter['secondary_type_id']);
        $this->assertNotNull($ivysaurAfter['primary_type_id']);
        $this->assertNotNull($ivysaurAfter['secondary_type_id']);
    }

    public function testImportNewAndExistingPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $this->getService()->execute('pokemon_list / new_and_existing');

        $this->assertEquals(34, $this->getPokemonCount());
        $this->assertEquals(17, $this->getPokemonNotDeletedCount());
        $this->assertEquals(17, $this->getPokemonDeletedCount());
    }

    public function testUpdateRegionalFormPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $pokemonBefore = $this->getPokemonFromName('Douze');
        $this->assertNull($pokemonBefore['regional_form_id']);

        $this->getService()->execute('pokemon_list / update_regional_form');

        $this->assertEquals(26, $this->getPokemonCount());
        $this->assertEquals(1, $this->getPokemonNotDeletedCount());
        $this->assertEquals(25, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');
        $this->assertNotNull($pokemonAfter['regional_form_id']);
    }

    public function testUpdateTypesPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $pokemonBefore = $this->getPokemonFromName('Douze');
        $this->assertNull($pokemonBefore['primary_type_id']);
        $this->assertNull($pokemonBefore['secondary_type_id']);

        $this->getService()->execute('pokemon_list / update_type');

        $this->assertEquals(26, $this->getPokemonCount());
        $this->assertEquals(1, $this->getPokemonNotDeletedCount());
        $this->assertEquals(25, $this->getPokemonDeletedCount());

        $pokemonAfter = $this->getPokemonFromName('Douze');
        $this->assertNotNull($pokemonAfter['primary_type_id']);
        $this->assertNotNull($pokemonAfter['secondary_type_id']);
    }

    public function testUpdateFamilyLink(): void
    {
        $this->assertNotEmpty($this->getPokemonFromName('Charmander'));
        $this->assertNotEmpty($this->getPokemonFromName('Charmeleon'));
        $this->assertEmpty($this->getPokemonFromName('Pidgey'));
        $this->assertEmpty($this->getPokemonFromName('Pidgeotto'));
        $this->assertEmpty($this->getPokemonFromName('Rattata'));
        $this->assertEmpty($this->getPokemonFromName('Raticate'));

        $this->getService()->execute('pokemon_list / family_link');

        // Testing updating existing family
        $charmander = $this->getPokemonFromName('Charmander');
        $charmeleon = $this->getPokemonFromName('Charmeleon');

        $this->assertEquals('charmander', $charmander['family']);
        $this->assertEquals('charmander', $charmeleon['family']);

        // Testing creating family
        $pidgey = $this->getPokemonFromName('Pidgey');
        $pidgeotto = $this->getPokemonFromName('Pidgeotto');

        $this->assertEquals('pidgey', $pidgey['family']);
        $this->assertEquals('pidgey', $pidgeotto['family']);

        // Testing creating family with gender
        $rattataMale = $this->getPokemonFromName('Rattata ♂️');
        $rattataFemale = $this->getPokemonFromName('Rattata ♀');
        $raticateMale = $this->getPokemonFromName('Raticate ♂️');
        $raticateFemale = $this->getPokemonFromName('Raticate ♀');

        $this->assertEquals('rattata', $rattataMale['family']);
        $this->assertEquals('rattata', $rattataFemale['family']);
        $this->assertEquals('rattata', $raticateMale['family']);
        $this->assertEquals('rattata', $raticateFemale['family']);
    }

    public function testDifferentColumnsOrderPokemons(): void
    {
        $this->assertGreaterThan(0, $this->getPokemonCount());
        $this->assertEquals($this->getPokemonCount(), $this->getPokemonNotDeletedCount());
        $this->assertEquals(0, $this->getPokemonDeletedCount());

        $this->getService()->execute('pokemon_list / different_columns_order');

        $this->assertEquals(34, $this->getPokemonCount());
        $this->assertEquals(17, $this->getPokemonNotDeletedCount());
        $this->assertEquals(17, $this->getPokemonDeletedCount());
    }

    #[\Override]
    protected function getService(): AbstractUpdater
    {
        /** @var PokemonsUpdater */
        return self::getContainer()->get(PokemonsUpdater::class);
    }
}
