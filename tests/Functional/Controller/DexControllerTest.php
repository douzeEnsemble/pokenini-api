<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;

class DexControllerTest extends AbstractControllerApiTest
{
    use GetTrainerDexTrait;

    public function testListUser12(): void
    {
        $this->apiRequest('GET', 'dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12Content(),
            $data
        );
    }

    public function testListUser12WithUnReleased(): void
    {
        $this->apiRequest(
            'GET',
            'dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_unreleased_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleased(),
            $data
        );
    }

    public function testListUser13(): void
    {
        $this->apiRequest('GET', 'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/list');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
        /** @var string[][]|string[][][]|int[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser13Content(),
            $data
        );
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', 'dex/46546542313186/list');

        $this->assertResponseIsOK();

        $content = $this->getResponseContent();
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
        $this->assertArrayHasKey('is_on_home', $trainerDexBefore);
        $this->assertFalse($trainerDexBefore['is_on_home']);
        $this->assertNull($trainerDexBefore['name']);
        $this->assertNull($trainerDexBefore['french_name']);
        $this->assertEmpty($trainerDexBefore['slug']);

        $this->apiRequest(
            'PUT',
            'dex/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            '{"is_private": true, "is_on_home": true}'
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_on_home']);
        $this->assertNull($trainerDexAfter['name']);
        $this->assertNull($trainerDexAfter['french_name']);
        $this->assertEmpty($trainerDexAfter['slug']);
    }

    public function testUpdateTrainerSlug(): void
    {
        $trainerDexBefore = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'homepogopokeball');

        $this->assertArrayHasKey('is_private', $trainerDexBefore);
        $this->assertFalse($trainerDexBefore['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexBefore);
        $this->assertTrue($trainerDexBefore['is_on_home']);
        $this->assertNull($trainerDexBefore['name']);
        $this->assertEquals('Home PoGo Poké Ball', $trainerDexBefore['french_name']);
        $this->assertEquals('homepogopokeball', $trainerDexBefore['slug']);

        $this->apiRequest(
            'PUT',
            'dex/7b52009b64fd0a2a49e6d8a939753077792b0554/homepogo/homepogopokeball',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            '{"is_private": true, "is_on_home": true}'
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'homepogopokeball');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_on_home']);
        $this->assertNull($trainerDexAfter['name']);
        $this->assertEquals('Home PoGo Poké Ball', $trainerDexAfter['french_name']);
        $this->assertEquals('homepogopokeball', $trainerDexAfter['slug']);
    }

    public function testCreate(): void
    {
        $trainerDexBefore = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertEmpty($trainerDexBefore);

        $this->apiRequest(
            'PUT',
            'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            '{"is_private": true, "is_on_home": false}'
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertFalse($trainerDexAfter['is_on_home']);
        $this->assertNull($trainerDexAfter['name']);
        $this->assertNull($trainerDexAfter['french_name']);
        $this->assertEmpty($trainerDexAfter['slug']);
    }

    public function testCreateWithMissingAttribute(): void
    {
        $trainerDexBefore = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertEmpty($trainerDexBefore);

        $this->apiRequest(
            'PUT',
            'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            '{"is_private": true}',
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertFalse($trainerDexAfter['is_on_home']);
        $this->assertNull($trainerDexAfter['name']);
        $this->assertNull($trainerDexAfter['french_name']);
        $this->assertEmpty($trainerDexAfter['slug']);
    }

    public function testBadArgument(): void
    {
        $this->apiRequest(
            'PUT',
            'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            '{"is_private": true, "isOnHome": false}',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEmptyData(): void
    {
        $this->apiRequest(
            'PUT',
            'dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze'],
            '',
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
