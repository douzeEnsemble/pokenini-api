<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\AlbumIndexFilteredController;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Traits\PokemonListTrait;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @psalm-import-type PokedexResponse from \App\Tests\Common\Types\PokedexTypes
 */
#[CoversClass(AlbumIndexController::class)]
final class CatchStatesTest extends AbstractTestAlbumIndexFilteredController
{
    use AssertReportTrait;
    use PokemonListTrait;

    public function testCatchStateFilter(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    'maybe',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertSameSlugs(
            $pokemons,
            [
                'caterpie',
                'rattata-f',
                'raticate-f',
            ],
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 3, 0, 0, 3);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testCatchStateFilterNegative(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    '!maybe',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertCount(19, $pokemons);

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 9, 0, 3, 7, 19);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testNoCatchStateFilter(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    'no',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertCount(9, $pokemons);

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 9, 0, 0, 0, 9);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testCatchStateFilterNegativeNo(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    '!no',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertCount(13, $pokemons);

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 3, 3, 7, 13);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testCatchStateFilterNull(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
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

        $this->assertCount(1, $pokemons);
    }

    public function testCatchStatesFilter(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'catch_states' => [
                    'maybe',
                    'maybenot',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertSameSlugs(
            $pokemons,
            [
                'caterpie',
                'metapod',
                'rattata-f',
                'rattata-alola',
                'raticate-f',
                'raticate-alola',
            ],
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 3, 3, 0, 6);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }
}
