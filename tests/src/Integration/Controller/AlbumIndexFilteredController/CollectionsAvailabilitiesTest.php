<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\AlbumIndexFilteredController;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Traits\PokemonListTrait;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 *
 * @psalm-import-type PokedexResponse from \App\Tests\Common\Types\PokedexTypes
 */
#[CoversClass(AlbumIndexController::class)]
final class CollectionsAvailabilitiesTest extends AbstractTestAlbumIndexFilteredController
{
    use AssertReportTrait;
    use PokemonListTrait;

    #[Test]
    public function collectionsAvailabilitiesFilter(): void
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
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertResponseSameSlugs(
            $pokemons,
            [
                'bulbasaur',
            ],
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 1, 0, 0, 0, 1);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    #[Test]
    public function collectionAvailabililiesNullFilter(): void
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
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertCount(0, $pokemons);
    }

    #[Test]
    public function collectionsAvailabilitiesNegativeFilter(): void
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
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertResponseSameSlugs(
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

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 8, 3, 3, 7, 21);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }
}
