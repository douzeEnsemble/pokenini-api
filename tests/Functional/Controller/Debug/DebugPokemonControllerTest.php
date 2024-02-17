<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Debug;

use App\Tests\Functional\Controller\AbstractTestControllerApi;

class DebugPokemonControllerTest extends AbstractTestControllerApi
{
    public function testPokemon(): void
    {
        $this->apiRequest('GET', 'debogage/pokemon/venusaur-mega');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);


        /** @var string[][]|int[][]|bool[][]|string[][][]|int[][][]|bool[][][]|string[][][][]|int[][][][]|bool[][][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('identifier', $data);
        $this->assertEquals('venusaur-mega', $data['slug']);

        $this->assertArrayHasKey('originalGameBundle', $data);
        $this->assertArrayHasKey('identifier', $data['originalGameBundle']);
        $this->assertEquals('xy', $data['originalGameBundle']['slug']);

        $this->assertNull($data['variantForm']);

        $this->assertNull($data['regionalForm']);

        $this->assertArrayHasKey('specialForm', $data);
        $this->assertArrayHasKey('identifier', $data['specialForm']);
        $this->assertEquals('mega', $data['specialForm']['slug']);

        $this->assertNull($data['categoryForm']);
    }

    public function testPokemonNotFound(): void
    {
        $this->apiRequest('GET', 'debogage/pokemon/venusaur-mega-x');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPokemonCleanCaches(): void
    {
        $this->apiRequest('DELETE', 'debogage/pokemon/venusaur-mega/caches');

        $this->assertResponseIsOK();

        $this->assertEmpty($this->getResponseContent());
    }

    public function testPokemonCleanCachesNotFound(): void
    {
        $this->apiRequest('DELETE', 'debogage/pokemon/venusaur-mega-x/caches');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPokemonAvailabilities(): void
    {
        $this->apiRequest('GET', 'debogage/pokemon/venusaur-mega/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var bool[][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('gamesAvailabilities', $data);
        $this->assertArrayNotHasKey('blue', $data['gamesAvailabilities']);
        $this->assertArrayNotHasKey('gold', $data['gamesAvailabilities']);
        $this->assertArrayHasKey('x', $data['gamesAvailabilities']);
        $this->assertIsBool($data['gamesAvailabilities']['x']);

        $this->assertArrayHasKey('gamesShiniesAvailabilities', $data);
        $this->assertArrayNotHasKey('blue', $data['gamesShiniesAvailabilities']);
        $this->assertArrayNotHasKey('gold', $data['gamesShiniesAvailabilities']);
        $this->assertArrayHasKey('x', $data['gamesShiniesAvailabilities']);
        $this->assertIsBool($data['gamesShiniesAvailabilities']['x']);

        $this->assertArrayHasKey('gameBundlesAvailabilities', $data);
        $this->assertArrayHasKey('goldsilvercrystal', $data['gameBundlesAvailabilities']);
        $this->assertIsBool($data['gameBundlesAvailabilities']['goldsilvercrystal']);

        $this->assertArrayHasKey('gameBundlesShiniesAvailabilities', $data);
        $this->assertArrayHasKey('goldsilvercrystal', $data['gameBundlesShiniesAvailabilities']);
        $this->assertIsBool($data['gameBundlesShiniesAvailabilities']['goldsilvercrystal']);
    }

    public function testPokemonAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', 'debogage/pokemon/venusaur-mega-x/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
