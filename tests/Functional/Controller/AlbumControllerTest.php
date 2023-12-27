<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use App\Tests\Functional\Controller\AlbumControllerTestData;

class AlbumControllerTest extends AbstractTestControllerApi
{
    use GetPokedexTrait;
    use CountTrainerDexTrait;

    public function testListUser12RedGreenBlueYellow(): void
    {
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertEquals('Kanto', $data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertEquals('Kanto', $data['dex']['region_french_name']);
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
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumControllerTestData::getExpectedRegGreenBlueYellowContent(
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

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 1, 1, 2, 0, 7);
    }

    public function testListUser12GoldSilverCrystal(): void
    {
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/goldsilvercrystal');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertEquals('Johto', $data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertEquals('Johto', $data['dex']['region_french_name']);
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
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumControllerTestData::getExpectedGoldSilverCrystalContent(
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

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 2, 0, 0, 1, 9);
    }

    public function testListUser13(): void
    {
        $this->apiRequest('GET', 'album/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertEquals('Kanto', $data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertEquals('Kanto', $data['dex']['region_french_name']);
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
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumControllerTestData::getExpectedRegGreenBlueYellowContent(
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

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 1, 7);
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', 'album/46546542313186/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertEquals('Kanto', $data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertEquals('Kanto', $data['dex']['region_french_name']);
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
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumControllerTestData::getExpectedRegGreenBlueYellowContent(
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

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 7);
    }

    public function testListHome(): void
    {
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertNull($data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertNull($data['dex']['region_french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230421.123456', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumControllerTestData::getExpectedHomeContent(),
            $pokemons
        );

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 6, 0, 0, 0, 12);
    }

    public function testListHomeShiny(): void
    {
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home_shiny');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertNull($data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertNull($data['dex']['region_french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.123456', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);

        $this->assertArrayHasKey('pokemons', $data);
        /** @var string[][] $pokemons */
        $pokemons = $data['pokemons'];

        $this->assertEquals(
            AlbumControllerTestData::getExpectedHomeShinyContent(),
            $pokemons
        );

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
        $report = $data['report'];

        $this->assertReport($report, 0, 0, 0, 0, 11);
    }

    public function testListHomePoGo(): void
    {
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/home_pogo');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertNull($data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertNull($data['dex']['region_french_name']);
        $this->assertArrayHasKey('description', $data['dex']);
        $this->assertEquals('', $data['dex']['description']);
        $this->assertArrayHasKey('french_description', $data['dex']);
        $this->assertEquals('', $data['dex']['french_description']);
        $this->assertArrayHasKey('version', $data['dex']);
        $this->assertEquals('20230321.121212', $data['dex']['version']);
        $this->assertArrayHasKey('is_released', $data['dex']);
        $this->assertTrue($data['dex']['is_released']);
    }

    public function testListHomeShinyOT(): void
    {
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/homeshinyot');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

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
        $this->assertArrayHasKey('region_name', $data['dex']);
        $this->assertNull($data['dex']['region_name']);
        $this->assertArrayHasKey('region_french_name', $data['dex']);
        $this->assertNull($data['dex']['region_french_name']);
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
        $this->apiRequest('GET', 'album/7b52009b64fd0a2a49e6d8a939753077792b0554/homepogo');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('dex', $data);
        $this->assertEmpty($data['dex']);
        $this->assertArrayHasKey('pokemons', $data);
        $this->assertEmpty($data['pokemons']);

        $this->assertArrayHasKey('report', $data);
        /** @var int[]|int[][][]|string[][][] $report */
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
        $this->apiRequest('GET', 'album/home', []);

        $this->assertResponseIsNotFound();

        $this->apiRequest('GET', 'album/home', []);

        $this->assertResponseIsNotFound();
    }

    public function testUpdate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexBefore);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $this->apiRequest(
            'PATCH',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'yes'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('yes', $pokedexAfter['slug']);

        $this->assertEquals(12, $this->getTrainerDexCount());
    }

    public function testUpdateEmpty(): void
    {
        $this->apiRequest(
            'PATCH',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PATCH',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'yes'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexAfter);
    }

    public function testUpdateNonExistingPokemon(): void
    {
        $this->apiRequest(
            'PATCH',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/douze',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(12, $this->getTrainerDexCount());
    }

    public function testCreateNonExistingTrainerDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(12, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/spoon/douze',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(13, $this->getTrainerDexCount());
    }

    public function testCreateNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PUT',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'yes'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexAfter);
    }

    public function testCreateNonExistingPokemon(): void
    {
        $this->apiRequest(
            'PUT',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateEmpty(): void
    {
        $this->apiRequest(
            'PUT',
            'album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
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
