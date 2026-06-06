<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
use App\Factory\DexAvailabilitiesResponseFactory;
use App\Factory\DexDebugResponseFactory;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DebugDexController::class)]
#[CoversClass(DexAvailabilitiesResponseFactory::class)]
#[CoversClass(DexAvailabilitiesService::class)]
#[CoversClass(DexDebugResponseFactory::class)]
final class DebugDexControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function testDex(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('identifier', $data);
        $this->assertEquals('redgreenblueyellow', $data['slug']);

        $this->assertArrayHasKey('region', $data);
        $this->assertArrayHasKey('identifier', $data['region']);
        $this->assertEquals('kanto', $data['region']['slug']);
    }

    #[Test]
    public function testDexNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs');

        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function testDexAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow/availabilities');

        $this->assertJsonResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|array{pokemons: string[]} $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('pokemons', $data);
        $this->assertContains('bulbasaur', $data['pokemons']);
        $this->assertContains('douze', $data['pokemons']);
    }

    #[Test]
    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
