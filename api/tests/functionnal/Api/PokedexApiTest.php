<?php

namespace App\Tests\Functionnal\Api;

class PokedexApiTest extends AbstractApiTest
{
    public function testList(): void
    {
        $response = $this->apiRequest('pokedex', ['dex.slug' => 'redgreenblueyellow']);

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][] $data */
        $data = json_decode($content, true);

        $this->assertArrayHasKey('pokemons', $data);
        $this->assertArrayHasKey('dex', $data);

        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);

        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals('bulbasaur', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('Bulbasaur', $pokemons[0]['pokemon_name']);
        $this->assertEquals('bulbasaur', $pokemons[0]['pokemon_icon']);
        $this->assertEquals('no', $pokemons[0]['catch_state_slug']);
        $this->assertEquals('No', $pokemons[0]['catch_state_name']);

        $this->assertEquals('venusaur', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('Venusaur', $pokemons[2]['pokemon_name']);
        $this->assertEquals('venusaur', $pokemons[2]['pokemon_icon']);
        $this->assertEquals('maybenot', $pokemons[2]['catch_state_slug']);
        $this->assertEquals('Maybe not', $pokemons[2]['catch_state_name']);

        $this->assertEquals('douze', $pokemons[3]['pokemon_slug']);
        $this->assertEquals('Douze', $pokemons[3]['pokemon_name']);
        $this->assertEquals('douze', $pokemons[3]['pokemon_icon']);
        $this->assertNull($pokemons[3]['catch_state_slug']);
        $this->assertNull($pokemons[3]['catch_state_name']);
    }

    public function testListNoSlug(): void
    {
        $response = $this->apiRequest('pokedex', []);

        $this->assertEquals(400, $response->getStatusCode());

        $response = $this->apiRequest('pokedex', ['dex.slug' => '']);

        $this->assertEquals(400, $response->getStatusCode());
    }
}
