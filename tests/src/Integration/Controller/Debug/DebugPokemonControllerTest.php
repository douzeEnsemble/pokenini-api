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

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);

        $this->assertArrayHasKey('games_availabilities', $data);
        $gamesSlugs = $this->getGameSlugs($data['games_availabilities']);
        $this->assertNotContains('blue', $gamesSlugs);
        $this->assertNotContains('gold', $gamesSlugs);
        $this->assertContains('x', $gamesSlugs);

        $this->assertArrayHasKey('games_shinies_availabilities', $data);
        $gamesShiniesSlugs = $this->getGameSlugs($data['games_shinies_availabilities']);
        $this->assertNotContains('blue', $gamesShiniesSlugs);
        $this->assertNotContains('gold', $gamesShiniesSlugs);
        $this->assertContains('x', $gamesShiniesSlugs);

        $this->assertArrayHasKey('game_bundles_availabilities', $data);
        $gameBundlesSlugs = $this->getGameBundleSlugs($data['game_bundles_availabilities']);
        $this->assertContains('goldsilvercrystal', $gameBundlesSlugs);

        $this->assertArrayHasKey('game_bundles_shinies_availabilities', $data);
        $gameBundlesShiniesSlugs = $this->getGameBundleSlugs($data['game_bundles_shinies_availabilities']);
        $this->assertContains('goldsilvercrystal', $gameBundlesShiniesSlugs);

        /** @var mixed $availabilities */
        foreach ($data as $availabilities) {
            $this->assertIsArray($availabilities);

            /** @var mixed $availability */
            foreach ($availabilities as $availability) {
                $this->assertIsArray($availability);
                $this->assertArrayHasKey('is_available', $availability);
                $this->assertIsBool($availability['is_available']);
            }
        }
    }

    public function testPokemonAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return string[]
     */
    private function getGameSlugs(mixed $availabilities): array
    {
        $this->assertIsArray($availabilities);

        $slugs = [];

        /** @var mixed $availability */
        foreach ($availabilities as $availability) {
            $this->assertIsArray($availability);
            $this->assertArrayHasKey('game', $availability);
            $this->assertIsArray($availability['game']);
            $this->assertArrayHasKey('slug', $availability['game']);
            $this->assertIsString($availability['game']['slug']);
            $slugs[] = $availability['game']['slug'];
        }

        return $slugs;
    }

    /**
     * @return string[]
     */
    private function getGameBundleSlugs(mixed $availabilities): array
    {
        $this->assertIsArray($availabilities);

        $slugs = [];

        /** @var mixed $availability */
        foreach ($availabilities as $availability) {
            $this->assertIsArray($availability);
            $this->assertArrayHasKey('game_bundle', $availability);
            $this->assertIsArray($availability['game_bundle']);
            $this->assertArrayHasKey('slug', $availability['game_bundle']);
            $this->assertIsString($availability['game_bundle']['slug']);
            $slugs[] = $availability['game_bundle']['slug'];
        }

        return $slugs;
    }
}
