<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AlbumIndexController;
use App\Tests\Common\Data\AlbumData;
use App\Tests\Common\Traits\ReportTrait\AssertReportTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @psalm-import-type PokedexResponse from \App\Tests\Common\Types\PokedexTypes
 */
#[CoversClass(AlbumIndexController::class)]
final class AlbumIndexControllerTest extends AbstractTestControllerApi
{
    use AssertReportTrait;

    public function testListUser12RedGreenBlueYellow(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertFalse($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Kanto', $data['dex']['region']['name']);
        $this->assertEquals('Kanto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowNestedContent(
                'no',
                'maybe',
                'maybenot',
                'maybenot',
                null,
                null,
                null
            ),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 4, 1, 2, 0, 7);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 4, 1, 2, 0, 7);
    }

    public function testListUser12GoldSilverCrystal(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/goldsilvercrystal');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('goldsilvercrystal', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('goldsilvercrystal', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Gold / Silver / Crystal', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Or / Argent / Cristal', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Johto', $data['dex']['region']['name']);
        $this->assertEquals('Johto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Gold, Silver and Crystal games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Or, Argent et Cristal.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertFalse($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedGoldSilverCrystalNestedContent(
                'yes',
                'no',
                'no',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 8, 0, 0, 1, 9);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 8, 0, 0, 1, 9);
    }

    public function testListUser13(): void
    {
        $this->apiRequest('GET', '/album/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Kanto', $data['dex']['region']['name']);
        $this->assertEquals('Kanto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowNestedContent(
                'yes',
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 6, 0, 0, 1, 7);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 6, 0, 0, 1, 7);
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', '/album/46546542313186/redgreenblueyellow');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertIsArray($data['dex']);

        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('redgreenblueyellow', $data['dex']['original_slug']);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Red / Green / Blue / Yellow', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_on_home', $data['dex']);
        $this->assertFalse($data['dex']['is_on_home']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertIsArray($data['dex']['region']);
        $this->assertEquals('Kanto', $data['dex']['region']['name']);
        $this->assertEquals('Kanto', $data['dex']['region']['french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $data['dex']['description']
        );
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $data['dex']['french_description']
        );
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230221.085100', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedRegGreenBlueYellowNestedContent(
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 0, 0, 0, 7);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 7);
    }

    public function testListHome(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('home', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertFalse($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230421.123456', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedHomeNestedContent(),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 9, 3, 3, 7, 22);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 9, 3, 3, 7, 22);
    }

    public function testListHomeShiny(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home_shiny');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home_shiny', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homeshiny', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals("Home\nShiny", $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals("Home\nChromatique", $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertTrue($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.123456', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);

        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumData::getExpectedHomeShinyNestedContent(),
            $pokemons
        );

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 11, 0, 0, 0, 11);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 11, 0, 0, 0, 11);
    }

    public function testListHomePoGo(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/home_pogo');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('home_pogo', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homepogo', $data['dex']['original_slug']);
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
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('list-7', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.121212', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertFalse($data['dex']['is_released']);
    }

    public function testListHomeShinyOT(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/homeshinyot');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data['dex']);
        $this->assertArrayHasKey('slug', $data['dex']);
        $this->assertEquals('homeshinyot', $data['dex']['slug']);
        $this->assertArrayHasKey('original_slug', $data['dex']);
        $this->assertEquals('homeshiny', $data['dex']['original_slug']);
        $this->assertArrayHasKey('dex', $data);
        $this->assertArrayHasKey('name', $data['dex']);
        $this->assertEquals('Home Shiny OT', $data['dex']['name']);
        $this->assertArrayHasKey('french_name', $data['dex']);
        $this->assertEquals('Home Chromatique OT', $data['dex']['french_name']);
        $this->assertArrayHasKey('is_shiny', $data['dex']);
        $this->assertTrue($data['dex']['is_shiny']);
        $this->assertArrayHasKey('is_private', $data['dex']);
        $this->assertTrue($data['dex']['is_private']);
        $this->assertArrayHasKey('is_display_form', $data['dex']);
        $this->assertTrue($data['dex']['is_display_form']);
        $this->assertArrayHasKey('display_template', $data['dex']);
        $this->assertEquals('box', $data['dex']['display_template']);
        $this->assertArrayHasKey('region', $data['dex']);
        $this->assertNull($data['dex']['region']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.123456', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);
    }

    public function testListMultipleHomePoGo(): void
    {
        $this->apiRequest('GET', '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/homepogo');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var PokedexResponse $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertNull($data['dex']);
        $this->assertArrayHasKey('pokemons', $data);
        $this->assertEmpty($data['pokemons']);

        $this->assertArrayHasKey('filtered_report', $data);

        $filteredReport = $data['filtered_report'];

        $this->assertReport($filteredReport, 0, 0, 0, 0, 0);

        $this->assertArrayHasKey('report', $data);

        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 0);
    }

    public function testListNoSlug(): void
    {
        $this->apiRequest('GET', 'album', []);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', 'album', ['dex.slug' => '']);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', 'album', ['dex.slug' => 'redgreenblueyellow']);

        $this->assertResponseIsNotFound();
    }

    public function testListNoUser(): void
    {
        $this->apiRequest('GET', '/album/home', []);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', '/album/home', []);

        $this->assertResponseIsNotFound();
    }
}
