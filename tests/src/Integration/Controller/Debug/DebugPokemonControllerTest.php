<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugPokemonController;
use App\Factory\PokemonDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DebugPokemonController::class)]
#[CoversClass(DexAvailabilitiesService::class)]
#[CoversClass(PokemonDebugResponseFactory::class)]
final class DebugPokemonControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function pokemon(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        $this->assertStringContainsString('"slug":"venusaur-mega",', $content);
        $this->assertStringContainsString('"slug":"6",', $content);
        $this->assertStringContainsString('"slug":"xy",', $content);
        $this->assertStringContainsString('"family":{"slug":', $content);
        $this->assertStringContainsString('"bank":{"bankable":', $content);
        $this->assertStringContainsString('"forms":{', $content);
        $this->assertStringContainsString('"variant":null', $content);
        $this->assertStringContainsString('"regional":null', $content);
        $this->assertStringContainsString('"category":null', $content);
        $this->assertStringContainsString('"slug":"mega",', $content);
        $this->assertStringContainsString('"types":{', $content);
        $this->assertStringContainsString('"slug":"grass",', $content);
        $this->assertStringContainsString('"slug":"poison",', $content);
    }

    #[Test]
    public function pokemonNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega-x');

        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function pokemonCleanCaches(): void
    {
        $this->apiRequest('DELETE', '/debogage/pokemon/venusaur-mega/caches');

        $this->assertResponseIsOK();

        $this->assertEmpty($this->getClientResponseContent());
    }

    #[Test]
    public function pokemonCleanCachesNotFound(): void
    {
        $this->apiRequest('DELETE', '/debogage/pokemon/venusaur-mega-x/caches');

        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function pokemonAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/pokemon/venusaur-mega/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);

        $this->assertArrayHasKey('games', $data);
        $this->assertIsArray($data['games']);
        $this->assertArrayHasKey('normal', $data['games']);
        $this->assertArrayHasKey('shiny', $data['games']);

        $gamesSlugs = $this->getGameSlugs($data['games']['normal']);
        $this->assertNotContains('blue', $gamesSlugs);
        $this->assertNotContains('gold', $gamesSlugs);
        $this->assertContains('x', $gamesSlugs);

        $gamesShiniesSlugs = $this->getGameSlugs($data['games']['shiny']);
        $this->assertNotContains('blue', $gamesShiniesSlugs);
        $this->assertNotContains('gold', $gamesShiniesSlugs);
        $this->assertContains('x', $gamesShiniesSlugs);

        $this->assertArrayHasKey('game_bundles', $data);
        $this->assertIsArray($data['game_bundles']);
        $this->assertArrayHasKey('normal', $data['game_bundles']);
        $this->assertArrayHasKey('shiny', $data['game_bundles']);

        $gameBundlesSlugs = $this->getGameBundleSlugs($data['game_bundles']['normal']);
        $this->assertContains('goldsilvercrystal', $gameBundlesSlugs);

        $gameBundlesShiniesSlugs = $this->getGameBundleSlugs($data['game_bundles']['shiny']);
        $this->assertContains('goldsilvercrystal', $gameBundlesShiniesSlugs);

        foreach (['normal', 'shiny'] as $variant) {
            /** @var mixed $availability */
            foreach ($data['games'][$variant] as $availability) {
                $this->assertIsArray($availability);
                $this->assertArrayHasKey('is_available', $availability);
                $this->assertIsBool($availability['is_available']);
            }

            /** @var mixed $availability */
            foreach ($data['game_bundles'][$variant] as $availability) {
                $this->assertIsArray($availability);
                $this->assertArrayHasKey('is_available', $availability);
                $this->assertIsBool($availability['is_available']);
            }
        }
    }

    #[Test]
    public function pokemonAvailabilitiesNotFound(): void
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
