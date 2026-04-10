<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\AlbumIndexFilteredController;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Traits\PokemonListTrait;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AlbumIndexController::class)]
class CollectionsAvailabilitiesTest extends AbstractTestAlbumIndexFilteredController
{
    use AssertReportTrait;
    use PokemonListTrait;

    public function testCollectionsAvailabilitiesFilter(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'collection_availabilities' => [
                    'pogoshadow',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 1, 0, 0, 0, 1);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testCollectionAvailabililiesNullFilter(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'collection_availabilities' => [
                    'null',
                ],
            ],
        );
        $this->assertResponseIsOK();
        $content = $this->getResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(0, $pokemons);
    }

    public function testCollectionsAvailabilitiesNegativeFilter(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'collection_availabilities' => [
                    '!pogoshadow',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertSameSlugs(
            $pokemons,
            [
                'ivysaur',
                'venusaur',
                'venusaur-f',
                'venusaur-mega',
                'venusaur-gmax',
                'charmander',
                'charmeleon',
                'charizard',
                'caterpie',
                'metapod',
                'butterfree',
                'butterfree-f',
                'butterfree-gmax',
                'rattata',
                'rattata-f',
                'rattata-alola',
                'raticate',
                'raticate-f',
                'raticate-alola',
                'raticate-alola-totem',
                'douze',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 8, 3, 3, 7, 21);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }
}
