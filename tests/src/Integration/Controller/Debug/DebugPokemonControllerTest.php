<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugPokemonController;
use App\Factory\PokemonDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DebugPokemonController::class)]
#[CoversClass(DexAvailabilitiesService::class)]
#[CoversClass(PokemonDebugResponseFactory::class)]
final class DebugPokemonControllerTest extends AbstractTestControllerApi
{
    public function testPokemon(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        $this->assertStringContainsString('"slug":"venusaur-mega",', $content);
        $this->assertStringContainsString('"slug":"6",', $content);
        $this->assertStringContainsString('"slug":"xy",', $content);
        $this->assertStringContainsString('"forms":{', $content);
        $this->assertStringContainsString('"variant":null', $content);
        $this->assertStringContainsString('"regional":null', $content);
        $this->assertStringContainsString('"category":null', $content);
        $this->assertStringContainsString('"slug":"mega",', $content);
        $this->assertStringContainsString('"types":{', $content);
        $this->assertStringContainsString('"slug":"grass",', $content);
        $this->assertStringContainsString('"slug":"poison",', $content);
    }

    public function testPokemonNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPokemonCleanCaches(): void
    {
        $this->apiRequest('DELETE', '/debogage/pokemon/venusaur-mega/caches');

        $this->assertResponseIsOK();

        $this->assertEmpty($this->getClientResponseContent());
    }

    public function testPokemonCleanCachesNotFound(): void
    {
        $this->apiRequest('DELETE', '/debogage/pokemon/venusaur-mega-x/caches');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testPokemonAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var ?bool[][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('gamesAvailabilities', $data);
        $this->assertArrayNotHasKey('blue', $data['gamesAvailabilities']);
        $this->assertArrayNotHasKey('gold', $data['gamesAvailabilities']);
        $this->assertArrayHasKey('x', $data['gamesAvailabilities']);

        $this->assertArrayHasKey('gamesShiniesAvailabilities', $data);
        $this->assertArrayNotHasKey('blue', $data['gamesShiniesAvailabilities']);
        $this->assertArrayNotHasKey('gold', $data['gamesShiniesAvailabilities']);
        $this->assertArrayHasKey('x', $data['gamesShiniesAvailabilities']);

        $this->assertArrayHasKey('gameBundlesAvailabilities', $data);
        $this->assertArrayHasKey('goldsilvercrystal', $data['gameBundlesAvailabilities']);

        $this->assertArrayHasKey('gameBundlesShiniesAvailabilities', $data);
        $this->assertArrayHasKey('goldsilvercrystal', $data['gameBundlesShiniesAvailabilities']);
    }

    public function testPokemonAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
