<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AlbumUpsertController;
use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(AlbumUpsertController::class)]
final class AlbumUpsertControllerTest extends AbstractTestControllerApi
{
    use GetPokedexTrait;
    use CountTrainerDexTrait;

    public function testUpdate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexBefore);
        $this->assertEquals('maybe', $pokedexBefore['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('redgreenblueyellow', 'ivysaur');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybe', $pokedexAfter['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    public function testUpdateEmpty(): void
    {
        $this->apiRequest(
            'PATCH',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
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
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
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
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreate(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/goldsilvercrystal/douze',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('goldsilvercrystal', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(34, $this->getTrainerDexCount());
    }

    public function testCreateNonExistingTrainerDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertEmpty($pokedexBefore);

        $this->assertEquals(34, $this->getTrainerDexCount());

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/spoon/douze',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'maybenot'
        );

        $this->assertResponseIsSuccessful();

        $pokedexAfter = $this->getPokedexFromSlugs('spoon', 'douze');

        $this->assertArrayHasKey('slug', $pokedexAfter);
        $this->assertEquals('maybenot', $pokedexAfter['slug']);

        $this->assertEquals(35, $this->getTrainerDexCount());
    }

    public function testCreateNonExistingDex(): void
    {
        $pokedexBefore = $this->getPokedexFromSlugs('douze', 'ivysaur');

        $this->assertEmpty($pokedexBefore);

        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/douze/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
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
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/treize',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            'yes'
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateEmpty(): void
    {
        $this->apiRequest(
            'PUT',
            '/album/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow/ivysaur',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            ''
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
