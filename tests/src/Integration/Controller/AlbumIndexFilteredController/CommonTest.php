<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\AlbumIndexFilteredController;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AlbumIndexController::class)]
final class CommonTest extends AbstractTestAlbumIndexFilteredController
{
    use AssertReportTrait;

    public function testEmptyFilters(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
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
                'original_game_bundles' => [
                    '',
                ],
                'game_bundle_availabilities' => [
                    '',
                ],
                'game_bundle_shiny_availabilities' => [
                    '',
                ],
                'families' => [
                    '',
                ],
            ],
        );

        $this->assertResponseIsOK();
        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('pokemons', $data);

        /** @var string[][]|string[][][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertCount(22, $pokemons);

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 9, 3, 3, 7, 22);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }
}
