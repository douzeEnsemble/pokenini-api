<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexController;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexController::class)]
final class DexControllerTest extends AbstractTestControllerApi
{
    use GetTrainerDexTrait;

    public function testListUser12(): void
    {
        $this->apiRequest('GET', '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
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
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_unreleased_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleased(),
            $data
        );
    }

    public function testListUser12WithPremium(): void
    {
        $this->apiRequest(
            'GET',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_premium_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithPremium(),
            $data
        );
    }

    public function testListUser12WithUnreleasedAndPremium(): void
    {
        $this->apiRequest(
            'GET',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list',
            [
                'include_unreleased_dex' => '1',
                'include_premium_dex' => '1',
            ]
        );

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleasedAndPremium(),
            $data
        );
    }

    public function testListUser13(): void
    {
        $this->apiRequest('GET', '/dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/list');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(
            DexControllerTestData::getUser13Content(),
            $data
        );
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', '/dex/46546542313186/list');

        $this->assertResponseIsOK();

        $content = $this->getClientResponseContent();

        /** @var int[][][]|string[][]|string[][][] $data */
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
        $this->assertEquals('Red / Green / Blue / Yellow', $trainerDexBefore['name']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $trainerDexBefore['french_name']);
        $this->assertEquals('redgreenblueyellow', $trainerDexBefore['slug']);

        $this->apiRequest(
            'PUT',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            '{"is_private": true, "is_on_home": true}'
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_on_home']);
        $this->assertEquals('Red / Green / Blue / Yellow', $trainerDexAfter['name']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $trainerDexAfter['french_name']);
        $this->assertEquals('redgreenblueyellow', $trainerDexAfter['slug']);
    }

    public function testUpdateTrainerSlug(): void
    {
        $trainerDexBefore = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'homepogopokeball');

        $this->assertArrayHasKey('is_private', $trainerDexBefore);
        $this->assertFalse($trainerDexBefore['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexBefore);
        $this->assertTrue($trainerDexBefore['is_on_home']);
        $this->assertEquals('Home PoGo Poké Ball', $trainerDexBefore['name']);
        $this->assertEquals('Home PoGo Poké Ball', $trainerDexBefore['french_name']);
        $this->assertEquals('homepogopokeball', $trainerDexBefore['slug']);

        $this->apiRequest(
            'PUT',
            '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/homepogopokeball',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            '{"is_private": true, "is_on_home": true}'
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('7b52009b64fd0a2a49e6d8a939753077792b0554', 'homepogopokeball');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_on_home']);
        $this->assertEquals('Home PoGo Poké Ball', $trainerDexAfter['name']);
        $this->assertEquals('Home PoGo Poké Ball', $trainerDexAfter['french_name']);
        $this->assertEquals('homepogopokeball', $trainerDexAfter['slug']);
    }

    public function testCreate(): void
    {
        $trainerDexBefore = $this->getTrainerDex('fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b', 'redgreenblueyellow');

        $this->assertEmpty($trainerDexBefore);

        $this->apiRequest(
            'PUT',
            '/dex/fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            '{"is_private": true, "is_on_home": false}'
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertFalse($trainerDexAfter['is_on_home']);
        $this->assertEquals('Red / Green / Blue / Yellow', $trainerDexAfter['name']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $trainerDexAfter['french_name']);
        $this->assertEquals('redgreenblueyellow', $trainerDexAfter['slug']);
    }

    public function testCreateWithMissingAttribute(): void
    {
        $trainerDexBefore = $this->getTrainerDex('fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b', 'redgreenblueyellow');

        $this->assertEmpty($trainerDexBefore);

        $this->apiRequest(
            'PUT',
            '/dex/fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            '{"is_private": true}',
        );

        $this->assertResponseIsOK();

        $trainerDexAfter = $this->getTrainerDex('fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b', 'redgreenblueyellow');

        $this->assertArrayHasKey('is_private', $trainerDexAfter);
        $this->assertTrue($trainerDexAfter['is_private']);
        $this->assertArrayHasKey('is_on_home', $trainerDexAfter);
        $this->assertFalse($trainerDexAfter['is_on_home']);
        $this->assertEquals('Red / Green / Blue / Yellow', $trainerDexAfter['name']);
        $this->assertEquals('Rouge / Vert / Bleu / Jaune', $trainerDexAfter['french_name']);
        $this->assertEquals('redgreenblueyellow', $trainerDexAfter['slug']);
    }

    public function testBadArgument(): void
    {
        $this->apiRequest(
            'PUT',
            '/dex/fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            '{"is_private": true, "isOnHome": false}',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEmptyData(): void
    {
        $this->apiRequest(
            'PUT',
            '/dex/fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b/redgreenblueyellow',
            [],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD],
            '',
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
