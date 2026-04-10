<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\AlbumIndexFilteredController;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Traits\PokemonListTrait;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AlbumIndexController::class)]
class FormsTest extends AbstractTestAlbumIndexFilteredController
{
    use AssertReportTrait;
    use PokemonListTrait;

    public function testCategoryForm(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'category_forms' => [
                    'starter',
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

        $this->assertSameSlugs(
            $pokemons,
            [
                'bulbasaur',
                'charmander',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 1, 0, 0, 1, 2);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testCategoryFormNull(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'category_forms' => [
                    'null',
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

        $this->assertCount(20, $pokemons);
    }

    public function testRegionalForm(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'regional_forms' => [
                    'alolan',
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

        $this->assertSameSlugs(
            $pokemons,
            [
                'rattata-alola',
                'raticate-alola',
                'raticate-alola-totem',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 1, 0, 2, 0, 3);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testRegionalFormNull(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'regional_forms' => [
                    'null',
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

        $this->assertCount(19, $pokemons);
    }

    public function testSpecialForm(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'special_forms' => [
                    'gigantamax',
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

        $this->assertSameSlugs(
            $pokemons,
            [
                'venusaur-gmax',
                'butterfree-gmax',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 2, 0, 0, 0, 2);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testSpecialFormNull(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'special_forms' => [
                    'null',
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

        $this->assertCount(18, $pokemons);
    }

    public function testSpecialsForm(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'special_forms' => [
                    'gigantamax',
                    'mega',
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

        $this->assertSameSlugs(
            $pokemons,
            [
                'venusaur-mega',
                'venusaur-gmax',
                'butterfree-gmax',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 3, 0, 0, 0, 3);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testVariantForm(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'variant_forms' => [
                    'gender',
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

        $this->assertSameSlugs(
            $pokemons,
            [
                'venusaur-f',
                'butterfree-f',
                'rattata-f',
                'raticate-f',
            ],
        );

        $this->assertArrayHasKey('filteredReport', $data);

        /** @var int[]|int[][][]|string[][][] $filteredReport */
        $filteredReport = $data['filteredReport'];

        $this->assertReport($filteredReport, 1, 2, 0, 1, 4);

        $this->assertArrayHasKey('report', $data);

        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testVariantFormNull(): void
    {
        $this->apiRequest(
            'GET',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home',
            [
                'variant_forms' => [
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
    }
}
