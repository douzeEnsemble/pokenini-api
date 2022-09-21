<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Api;

use App\Tests\Resources\Traits\GetterTrait\GetPokedexTrait;
use App\Tests\Resources\Traits\AssertReportTrait;

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
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumApiTestData::getExpectedRegGreenBlueYellowContent(),
            $pokemons
        );

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 1, 2, 0, 7);
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
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumApiTestData::getExpectedHomeContent(),
            $pokemons
        );

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 6, 0, 0, 0, 12);
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
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumApiTestData::getExpectedHomeShinyContent(),
            $pokemons
        );

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 11);
    }

    public function testListHomePoGo(): void
    {
        $response = $this->apiRequest('album/homepogo');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home PoGo', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home PoGo', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertFalse($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertFalse($data['dex']['is_display_form']);
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
     * @param int[]|int[][][]|string[][][] $report
     */
    private function assertReport(
        array $report,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $this->assertArrayHasKey('total', $report);
        $this->assertEquals($countTotal, $report['total']);

        $this->assertArrayHasKey('totalCaught', $report);
        $this->assertEquals($countYes, $report['totalCaught']);

        $this->assertArrayHasKey('totalUncaught', $report);
        $this->assertEquals($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['totalUncaught']);

        $this->assertArrayHasKey('detail', $report);
        /** @var int[][]|string[][] $reportDetail */
        $reportDetail = $report['detail'];

        $this->assertArrayHasKey(0, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[0]);
        $this->assertEquals($countNo, $reportDetail[0]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[0]);
        $this->assertEquals('no', $reportDetail[0]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[0]);
        $this->assertEquals('No', $reportDetail[0]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[0]);
        $this->assertEquals('Non', $reportDetail[0]['frenchName']);

        $this->assertArrayHasKey(1, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[1]);
        $this->assertEquals($countMaybe, $reportDetail[1]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[1]);
        $this->assertEquals('maybe', $reportDetail[1]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[1]);
        $this->assertEquals('Maybe', $reportDetail[1]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[1]);
        $this->assertEquals('Peut être', $reportDetail[1]['frenchName']);

        $this->assertArrayHasKey(2, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[2]);
        $this->assertEquals($countMaybeNot, $reportDetail[2]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[2]);
        $this->assertEquals('maybenot', $reportDetail[2]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[2]);
        $this->assertEquals('Maybe not', $reportDetail[2]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[2]);
        $this->assertEquals('Peut être pas', $reportDetail[2]['frenchName']);

        $this->assertArrayHasKey(3, $reportDetail);
        $this->assertArrayHasKey('count', $reportDetail[3]);
        $this->assertEquals($countYes, $reportDetail[3]['count']);
        $this->assertArrayHasKey('slug', $reportDetail[3]);
        $this->assertEquals('yes', $reportDetail[3]['slug']);
        $this->assertArrayHasKey('name', $reportDetail[3]);
        $this->assertEquals('Yes', $reportDetail[3]['name']);
        $this->assertArrayHasKey('frenchName', $reportDetail[3]);
        $this->assertEquals('Oui', $reportDetail[3]['frenchName']);
    }
}
