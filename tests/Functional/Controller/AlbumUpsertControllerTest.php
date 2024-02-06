<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\CounterTrait\CountTrainerDexTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;

class AlbumUpsertControllerTest extends AbstractTestControllerApi
{
    use GetPokedexTrait;
    use CountTrainerDexTrait;

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
}
