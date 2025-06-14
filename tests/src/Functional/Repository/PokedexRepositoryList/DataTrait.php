<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository\PokedexRepositoryList;

trait DataTrait
{
    /**
     * @param bool[][]|int[][]|null[][]|string[][] $pokedex
     */
    public function assertPokedexBulbasaur(array $pokedex): void
    {
        $this->assertEquals('Bulbasaur', $pokedex[0]['pokemon_name']);
        $this->assertEquals('Bulbizarre', $pokedex[0]['pokemon_french_name']);
        $this->assertEquals('bulbasaur', $pokedex[0]['pokemon_slug']);
        $this->assertEquals('No', $pokedex[0]['catch_state_name']);
        $this->assertEquals('no', $pokedex[0]['catch_state_slug']);
        $this->assertEquals('Grass', $pokedex[0]['primary_type_name']);
        $this->assertEquals('Plante', $pokedex[0]['primary_type_french_name']);
        $this->assertEquals('grass', $pokedex[0]['primary_type_slug']);
        $this->assertEquals('Poison', $pokedex[0]['secondary_type_name']);
        $this->assertEquals('Poison', $pokedex[0]['secondary_type_french_name']);
        $this->assertEquals('poison', $pokedex[0]['secondary_type_slug']);
        $this->assertEquals('poison', $pokedex[0]['secondary_type_slug']);
        $this->assertEquals('redgreenblueyellow', $pokedex[0]['original_game_bundle_slug']);
        $this->assertEquals(
            'redgreenblueyellow,goldsilvercrystal',
            $pokedex[0]['game_bundle_slugs']
        );
        $this->assertEquals(
            'redgreenblueyellow,goldsilvercrystal',
            $pokedex[0]['game_bundle_shiny_slugs']
        );
    }

    /**
     * @param bool[][]|int[][]|null[][]|string[][] $pokedex
     */
    public function assertPokedexIvysaur(array $pokedex): void
    {
        $this->assertEquals('Ivysaur', $pokedex[1]['pokemon_name']);
        $this->assertEquals('Herbizarre', $pokedex[1]['pokemon_french_name']);
        $this->assertEquals('ivysaur', $pokedex[1]['pokemon_slug']);
        $this->assertEquals('Maybe', $pokedex[1]['catch_state_name']);
        $this->assertEquals('maybe', $pokedex[1]['catch_state_slug']);
        $this->assertEquals('Grass', $pokedex[1]['primary_type_name']);
        $this->assertEquals('Plante', $pokedex[1]['primary_type_french_name']);
        $this->assertEquals('grass', $pokedex[1]['primary_type_slug']);
        $this->assertEquals('Poison', $pokedex[1]['secondary_type_name']);
        $this->assertEquals('Poison', $pokedex[1]['secondary_type_french_name']);
        $this->assertEquals('poison', $pokedex[1]['secondary_type_slug']);
        $this->assertEquals('redgreenblueyellow', $pokedex[1]['original_game_bundle_slug']);
        $this->assertEquals('redgreenblueyellow,goldsilvercrystal', $pokedex[1]['game_bundle_slugs']);
        $this->assertEquals('redgreenblueyellow,goldsilvercrystal', $pokedex[1]['game_bundle_shiny_slugs']);
    }

    /**
     * @param bool[][]|int[][]|null[][]|string[][] $pokedex
     */
    public function assertPokedexVenusaur(array $pokedex): void
    {
        $this->assertEquals('Venusaur', $pokedex[2]['pokemon_name']);
        $this->assertEquals('Florizarre', $pokedex[2]['pokemon_french_name']);
        $this->assertEquals('venusaur', $pokedex[2]['pokemon_slug']);
        $this->assertEquals('Maybe not', $pokedex[2]['catch_state_name']);
        $this->assertEquals('maybenot', $pokedex[2]['catch_state_slug']);
        $this->assertEquals('Grass', $pokedex[2]['primary_type_name']);
        $this->assertEquals('Plante', $pokedex[2]['primary_type_french_name']);
        $this->assertEquals('grass', $pokedex[2]['primary_type_slug']);
        $this->assertEquals('Poison', $pokedex[2]['secondary_type_name']);
        $this->assertEquals('Poison', $pokedex[2]['secondary_type_french_name']);
        $this->assertEquals('poison', $pokedex[2]['secondary_type_slug']);
        $this->assertEquals('redgreenblueyellow', $pokedex[2]['original_game_bundle_slug']);
        $this->assertEquals('redgreenblueyellow,goldsilvercrystal', $pokedex[2]['game_bundle_slugs']);
        $this->assertEquals('redgreenblueyellow,goldsilvercrystal', $pokedex[2]['game_bundle_shiny_slugs']);
    }

    /**
     * @param bool[][]|int[][]|null[][]|string[][] $pokedex
     */
    public function assertPokedexCaterpie(array $pokedex): void
    {
        $this->assertEquals('Caterpie', $pokedex[3]['pokemon_name']);
        $this->assertEquals('Chenipan', $pokedex[3]['pokemon_french_name']);
        $this->assertEquals('caterpie', $pokedex[3]['pokemon_slug']);
        $this->assertEquals('Maybe not', $pokedex[3]['catch_state_name']);
        $this->assertEquals('maybenot', $pokedex[3]['catch_state_slug']);
        $this->assertEquals('Bug', $pokedex[3]['primary_type_name']);
        $this->assertEquals('Insecte', $pokedex[3]['primary_type_french_name']);
        $this->assertEquals('bug', $pokedex[3]['primary_type_slug']);
        $this->assertNull($pokedex[3]['secondary_type_name']);
        $this->assertNull($pokedex[3]['secondary_type_french_name']);
        $this->assertNull($pokedex[3]['secondary_type_slug']);
        $this->assertEquals('redgreenblueyellow', $pokedex[3]['original_game_bundle_slug']);
        $this->assertEquals(
            '',
            $pokedex[3]['game_bundle_slugs']
        );
        $this->assertEquals(
            '',
            $pokedex[3]['game_bundle_shiny_slugs']
        );
    }

    /**
     * @param bool[][]|int[][]|null[][]|string[][] $pokedex
     */
    public function assertPokedexDouze(array $pokedex): void
    {
        $this->assertEquals('Douze', $pokedex[6]['pokemon_name']);
        $this->assertEquals('Douze', $pokedex[6]['pokemon_french_name']);
        $this->assertEquals('douze', $pokedex[6]['pokemon_slug']);
        $this->assertNull($pokedex[6]['catch_state_name']);
        $this->assertNull($pokedex[6]['catch_state_slug']);
        $this->assertNull($pokedex[6]['primary_type_name']);
        $this->assertNull($pokedex[6]['primary_type_french_name']);
        $this->assertNull($pokedex[6]['primary_type_slug']);
        $this->assertNull($pokedex[6]['secondary_type_name']);
        $this->assertNull($pokedex[6]['secondary_type_french_name']);
        $this->assertNull($pokedex[6]['secondary_type_slug']);
        $this->assertEquals('redgreenblueyellow', $pokedex[6]['original_game_bundle_slug']);
        $this->assertEquals('un,dos,tres', $pokedex[6]['game_bundle_slugs']);
        $this->assertEquals('', $pokedex[6]['game_bundle_shiny_slugs']);
    }
}
