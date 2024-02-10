<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;

class AlbumIndexFilteredControllerTest extends AbstractTestControllerApi
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

    public function testCategoryForm(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'category_forms' => [
                    'starter',
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

        $this->assertCount(2, $pokemons);
        $this->assertEquals('bulbasaur', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('charmander', $pokemons[1]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 0, 0, 1, 2);
    }

    public function testCategoryFormNull(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'category_forms' => [
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

        $this->assertCount(20, $pokemons);
    }

    public function testRegionalForm(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'regional_forms' => [
                    'alolan',
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

    public function testRegionalFormNull(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'regional_forms' => [
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

        $this->assertCount(19, $pokemons);
    }

    public function testSpecialForm(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'special_forms' => [
                    'gigantamax',
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

        $this->assertCount(2, $pokemons);
        $this->assertEquals('venusaur-gmax', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokemons[1]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 2, 0, 0, 0, 2);
    }

    public function testSpecialFormNull(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'special_forms' => [
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

        $this->assertCount(18, $pokemons);
    }

    public function testSpecialsForm(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'special_forms' => [
                    'gigantamax',
                    'mega',
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
        $this->assertEquals('venusaur-mega', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('venusaur-gmax', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('butterfree-gmax', $pokemons[2]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 3, 0, 0, 0, 3);
    }

    public function testVariantForm(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'variant_forms' => [
                    'gender',
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

        $this->assertCount(4, $pokemons);
        $this->assertEquals('venusaur-f', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('butterfree-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokemons[3]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 2, 0, 1, 4);
    }

    public function testVariantFormNull(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'variant_forms' => [
                    '',
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

        $this->assertCount(22, $pokemons);
    }

    public function testCatchStateFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    'maybe',
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
        $this->assertEquals('caterpie', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokemons[2]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 3, 0, 0, 3);
    }

    public function testCatchStateFilterNull(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
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

    public function testCatchStatesFilter(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    'maybe',
                    'maybenot',
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
        $this->assertEquals('caterpie', $pokemons[0]['pokemon_slug']);
        $this->assertEquals('metapod', $pokemons[1]['pokemon_slug']);
        $this->assertEquals('rattata-f', $pokemons[2]['pokemon_slug']);
        $this->assertEquals('rattata-alola', $pokemons[3]['pokemon_slug']);
        $this->assertEquals('raticate-f', $pokemons[4]['pokemon_slug']);
        $this->assertEquals('raticate-alola', $pokemons[5]['pokemon_slug']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 3, 3, 0, 6);
    }

    public function testEmptyFilters(): void
    {
        $this->apiRequest(
            'GET',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'primary_types' => [
                    '',
                ],
                'secondary_types' => [
                    '',
                ],
                'any_types' => [
                    '',
                ],
                'category_forms' => [
                    '',
                ],
                'regional_forms' => [
                    '',
                ],
                'special_forms' => [
                    '',
                ],
                'variant_forms' => [
                    '',
                ],
                'catch_states' => [
                    '',
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

        $this->assertCount(22, $pokemons);
    }
}
