<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\AlbumIndexFilteredController;

use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;

class CommonTest extends AbstractIndexFilteredControllerTest
{
    use AssertReportTrait;

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
                'original_game_bundles' => [
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
