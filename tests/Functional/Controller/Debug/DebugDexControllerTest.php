<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Debug;

use App\Tests\Functional\Controller\AbstractTestControllerApi;

class DebugDexControllerTest extends AbstractTestControllerApi
{
    public function testDex(): void
    {
        $this->apiRequest('GET', 'debogage/dex/redgreenblueyellow');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var string[][]|int[][]|bool[][]|string[][][]|int[][][]|bool[][][] $data */
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
        $this->apiRequest('GET', 'debogage/dex/homeshinyapriballs');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDexAvailabilities(): void
    {
        $this->apiRequest('GET', 'debogage/dex/redgreenblueyellow/availabilities');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();

        $this->assertStringNotContainsString('__', $content);

        $this->assertJson($content);

        /** @var string[] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotNull($data);

        $this->assertContains('bulbasaur', $data);
        $this->assertContains('douze', $data);
    }

    public function testDexAvailabilitiesNotFound(): void
    {
        $this->apiRequest('GET', 'debogage/dex/homeshinyapriballs/availabilities');

        $this->assertResponseStatusCodeSame(404);
    }
}
