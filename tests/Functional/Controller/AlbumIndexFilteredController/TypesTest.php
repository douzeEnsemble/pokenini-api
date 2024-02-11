<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\AlbumIndexFilteredController;

use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;

class TypesTest extends AbstractIndexFilteredControllerTest
{
    use AssertReportTrait;

    public function testPrimaryTypeFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'primary_types' => [
                    'grass',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(6, $pokemons);
        $this->assertEquals('bulbasaur', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('ivysaur', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('venusaur', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('venusaur-f', $pokemons[3]['pokemon_slug']);
        $this->assertEquals('venusaur-mega', $pokemons[4]['pokemon_slug']);
        $this->assertEquals('venusaur-gmax', $pokemons[5]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 6, 0, 0, 0, 6);
    }

    public function testPrimaryTypeNullFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'primary_types' => [
                    'null',
                ],
            ],
        );
        $this->assertResponseIsOK();
        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(1, $pokemons);
    }

    public function testSecondaryTypeFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'secondary_types' => [
                    'normal',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(3, $pokemons);
        $this->assertEquals('rattata-alola', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokemons[2]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 0, 2, 0, 3);
    }

    public function testSecondaryTypeNullFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'secondary_types' => [
                    'null',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(9, $pokemons);
    }

    public function testPrimaryAndSecondaryTypeFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'primary_types' => [
                    'bug',
                ],
                'secondary_types' => [
                    'flying',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(3, $pokemons);
        $this->assertEquals('butterfree', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('butterfree-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokemons[2]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 0, 0, 2, 3);
    }

    public function testAnyTypeFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'any_types' => [
                    'normal',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(7, $pokemons);
        $this->assertEquals('rattata', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('rattata-alola', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('raticate', $pokemons[3]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokemons[4]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokemons[5]['pokemon_slug']);
        $this->assertEquals('raticate-alola-totem', $pokemons[6]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 2, 2, 2, 7);
    }
}
