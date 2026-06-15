<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
use App\DTO\Response\DexDebugOrderingResponse;
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
#[CoversClass(DexDebugOrderingResponse::class)]
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

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('identifier', $data);
        $this->assertEquals('redgreenblueyellow', $data['slug']);

        $this->assertArrayHasKey('region', $data);

        /** @var array<string, mixed> $region */
        $region = $data['region'];
        $this->assertArrayHasKey('identifier', $region);
        $this->assertEquals('kanto', $region['slug']);

        $this->assertArrayHasKey('flags', $data);

        /** @var array<string, mixed> $flags */
        $flags = $data['flags'];
        $this->assertArrayHasKey('is_shiny', $flags);
        $this->assertArrayHasKey('is_premium', $flags);
        $this->assertArrayHasKey('is_display_form', $flags);
        $this->assertArrayHasKey('is_released', $flags);
        $this->assertArrayHasKey('can_hold_election', $flags);

        $this->assertArrayNotHasKey('is_shiny', $data);
        $this->assertArrayNotHasKey('is_premium', $data);
        $this->assertArrayNotHasKey('is_display_form', $data);
        $this->assertArrayNotHasKey('is_released', $data);
        $this->assertArrayNotHasKey('can_hold_election', $data);

        $this->assertArrayHasKey('ordering', $data);

        /** @var array<string, int> $ordering */
        $ordering = $data['ordering'];
        $this->assertArrayHasKey('main', $ordering);
        $this->assertArrayHasKey('election', $ordering);

        $this->assertArrayNotHasKey('order_number', $data);
        $this->assertArrayNotHasKey('election_order_number', $data);
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

        /** @var null|array{pokemons: array{slug: string}[]} $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertArrayHasKey('pokemons', $data);
        $this->assertNotEmpty($data['pokemons']);

        $slugs = array_column($data['pokemons'], 'slug');
        $this->assertContains('bulbasaur', $slugs);
        $this->assertContains('douze', $slugs);
    }

    #[Test]
    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
