<?php

namespace App\Tests\Functionnal\Api;

use App\Tests\Resources\functionnal\GetterTrait\GetPokedexTrait;

class AlbumApiTest extends AbstractApiTest
{
    use GetPokedexTrait;

    public function testList(): void
    {
        $response = $this->apiRequest('album/redgreenblueyellow');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);

        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumApiTestData::getExpectedRegGreenBlueYellowContent(),
            $pokemons
        );
    }

    public function testListHome(): void
    {
        $response = $this->apiRequest('album/home');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertFalse($data['dex']['is_private']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumApiTestData::getExpectedHomeContent(),
            $pokemons
        );
    }

    public function testListHomeShiny(): void
    {
        $response = $this->apiRequest('album/homeshiny');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][] $data */
        $data = json_decode($content, true);

        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home Shiny', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home Chromatique', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertTrue($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertFalse($data['dex']['is_private']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals([], $pokemons);
    }

    public function testListNoSlug(): void
    {
        $response = $this->apiRequest('album', []);

        $this->assertEquals(404, $response->getStatusCode());

        $response = $this->apiRequest('album', ['dex.slug' => '']);

        $this->assertEquals(404, $response->getStatusCode());

        $response = $this->apiRequest('album', ['dex.slug' => 'redgreenblueyellow']);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexBefore);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        $this->apiRequest(
            'album/redgreenblueyellow/ivysaur',
            [],
            'PATCH',
            [
                'body' => 'yes'
            ]
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('yes', $pokedexAfter['slug']);
    }

    public function testCreate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'album/redgreenblueyellow/douze',
            [],
            'PUT',
            [
                'body' => 'maybenot'
            ]
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);
    }
}
