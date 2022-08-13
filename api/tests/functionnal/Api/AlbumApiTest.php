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
        /** @var string[][]|string[][][]|int[][][] $data */
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

        $this->assertArrayHasKey('report', $data);
        /** @var string[][]|int[][] $report */
        $report = $data['report'];

        $this->assertReport($report, 4, 1, 2, 0);
    }

    public function testListHome(): void
    {
        $response = $this->apiRequest('album/home');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
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

        $this->assertArrayHasKey('report', $data);
        /** @var string[][]|int[][] $report */
        $report = $data['report'];

        $this->assertReport($report, 12, 0, 0, 0);
    }

    public function testListHomeShiny(): void
    {
        $response = $this->apiRequest('album/homeshiny');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

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

        $this->assertArrayHasKey('report', $data);
        /** @var string[][]|int[][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0);
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
    /**
     * @param string[][]|int[][] $report
     */
    public function assertReport(array $report, int $countNo, int $countMaybe, int $countMaybeNot, int $countYes): void
    {
        $this->assertEquals(
            [
                'no',
                'maybe',
                'maybenot',
                'yes',
            ],
            array_keys($report)
        );

        $this->assertArrayHasKey('no', $report);
        $this->assertArrayHasKey('count', $report['no']);
        $this->assertEquals($countNo, $report['no']['count']);
        $this->assertArrayHasKey('name', $report['no']);
        $this->assertEquals('No', $report['no']['name']);
        $this->assertArrayHasKey('french_name', $report['no']);
        $this->assertEquals('Non', $report['no']['french_name']);

        $this->assertArrayHasKey('maybe', $report);
        $this->assertArrayHasKey('count', $report['maybe']);
        $this->assertEquals($countMaybe, $report['maybe']['count']);
        $this->assertArrayHasKey('name', $report['maybe']);
        $this->assertEquals('Maybe', $report['maybe']['name']);
        $this->assertArrayHasKey('french_name', $report['maybe']);
        $this->assertEquals('Peut être', $report['maybe']['french_name']);

        $this->assertArrayHasKey('maybenot', $report);
        $this->assertArrayHasKey('count', $report['maybenot']);
        $this->assertEquals($countMaybeNot, $report['maybenot']['count']);
        $this->assertArrayHasKey('name', $report['maybenot']);
        $this->assertEquals('Maybe not', $report['maybenot']['name']);
        $this->assertArrayHasKey('french_name', $report['maybenot']);
        $this->assertEquals('Peut être pas', $report['maybenot']['french_name']);

        $this->assertArrayHasKey('yes', $report);
        $this->assertArrayHasKey('count', $report['yes']);
        $this->assertEquals($countYes, $report['yes']['count']);
        $this->assertArrayHasKey('name', $report['yes']);
        $this->assertEquals('Yes', $report['yes']['name']);
        $this->assertArrayHasKey('french_name', $report['yes']);
        $this->assertEquals('Oui', $report['yes']['french_name']);
    }
}
