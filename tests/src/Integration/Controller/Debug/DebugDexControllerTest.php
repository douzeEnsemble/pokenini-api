<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller\Debug;

use App\Controller\Debug\DebugDexController;
use App\Service\DexAvailabilitiesService;
use App\Tests\Integration\Controller\AbstractTestControllerApi;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DebugDexController::class)]
#[CoversClass(DexAvailabilitiesService::class)]
final class DebugDexControllerTest extends AbstractTestControllerApi
{
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

    public function testDexNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDexAvailabilities(): void
    {
        $this->apiRequest('GET', '/debogage/dex/redgreenblueyellow/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var null|string[] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertContains('bulbasaur', $data);
        $this->assertContains('douze', $data);
    }

    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', '/debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
