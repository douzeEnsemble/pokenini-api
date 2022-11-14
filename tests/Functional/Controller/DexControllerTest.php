<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use App\Tests\Functional\Api\AbstractApiTest;

class DexControllerTest extends AbstractApiTest
{
    use GetTrainerDexTrait;

    public function testListUser12(): void
    {
        $response = $this->apiRequest('dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12Content(),
            $data
        );
    }

    public function testListUser13(): void
    {
        $response = $this->apiRequest('dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/list');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser13Content(),
            $data
        );
    }

    public function testListUserUnknown(): void
    {
        $response = $this->apiRequest('dex/46546542313186/list');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUserUnknownContent(),
            $data
        );
    }

    public function testUpdate(): void
    {
        $trainerDexBefore = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexBefore);
        $this->assertFalse($trainerDexBefore['is_private']);

        $this->apiRequest(
            'dex/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow',
            [],
            'PATCH',
            [
                'body' => '{"isPrivate": true}'
            ]
        );

        $this->assertResponseIsSuccessful();

        $trainerDexAfter = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
    }

    public function testCreate(): void
    {
        $trainerDexBefore = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertEmpty($trainerDexBefore);

        $this->apiRequest(
            'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow',
            [],
            'PATCH',
            [
                'body' => '{"isPrivate": true}'
            ]
        );

        $this->assertResponseIsSuccessful();

        $trainerDexAfter = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
    }

    public function testBadArgument(): void
    {
        $trainerDexBefore = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertEmpty($trainerDexBefore);

        $this->apiRequest(
            'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow',
            [],
            'PATCH',
            [
                'body' => '{"is_private": true}'
            ]
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
