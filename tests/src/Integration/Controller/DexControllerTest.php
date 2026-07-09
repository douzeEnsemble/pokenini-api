<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexController;
use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\TrainerDexSettingsResponse;
use App\Factory\TrainerDexResponseFactory;
use App\Tests\Common\Traits\GetterTrait\GetTrainerDexTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DexController::class)]
#[CoversClass(TrainerDexResponseFactory::class)]
#[CoversClass(DexFlagsResponse::class)]
#[CoversClass(TrainerDexSettingsResponse::class)]
final class DexControllerTest extends AbstractTestControllerApi
{
    use GetTrainerDexTrait;

    /** @var array<string, array<string, mixed>> */
    private array $lastResponseReportsBySlug = [];

    public function testListUser12(): void
    {
        $this->apiRequest('GET', '/dex/7b52009b64fd0a2a49e6d8a939753077792b0554/list');

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12Content(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
        $this->assertKnownReport('home_shiny', 11, 0, 0, 0, 11);
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

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleased(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
        $this->assertKnownReport('goldsilvercrystal', 8, 0, 0, 1, 9);
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

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithPremium(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
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

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser12ContentWithUnreleasedAndPremium(),
            $data
        );
        $this->assertKnownReport('home', 9, 3, 3, 7, 22);
        $this->assertKnownReport('goldsilvercrystal', 8, 0, 0, 1, 9);
    }

    public function testListUser13(): void
    {
        $this->apiRequest('GET', '/dex/bd307a3ec329e10a2cff8fb87480823da114f8f4/list');

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUser13Content(),
            $data
        );
        $this->assertReportShapeIsWellFormed();
    }

    public function testListUserUnknown(): void
    {
        $this->apiRequest('GET', '/dex/46546542313186/list');

        $this->assertResponseIsOK();

        $data = $this->getJsonDecodedResponseContentStrippedOfReports();

        $this->assertEquals(
            DexControllerTestData::getUserUnknownContent(),
            $data
        );
        $this->assertReportShapeIsWellFormed();
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

    /**
     * @return array<int, array<string, array<string, bool>|string|string[]>>
     */
    private function getJsonDecodedResponseContentStrippedOfReports(): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = $this->getJsonDecodedResponseContent();

        $this->lastResponseReportsBySlug = [];
        foreach ($data as $index => $entry) {
            /** @var array{report: array<string, mixed>, settings: array{slug: string}} $typedEntry */
            $typedEntry = $entry;
            $this->lastResponseReportsBySlug[$typedEntry['settings']['slug']] = $typedEntry['report'];
            unset($data[$index]['report']);
        }

        /** @var array<int, array<string, array<string, bool>|string|string[]>> */
        return $data;
    }

    private function assertKnownReport(
        string $dexSlug,
        int $countNo,
        int $countMaybe,
        int $countMaybeNot,
        int $countYes,
        int $countTotal
    ): void {
        $this->assertArrayHasKey($dexSlug, $this->lastResponseReportsBySlug);

        /** @var array{total: int, total_caught: int, total_uncaught: int, detail: array<int, array{catch_state: array{slug: string}, count: int}>} $report */
        $report = $this->lastResponseReportsBySlug[$dexSlug];

        $this->assertSame($countTotal, $report['total']);
        $this->assertSame($countYes, $report['total_caught']);
        $this->assertSame($countTotal - $countMaybe - $countMaybeNot - $countYes, $report['total_uncaught']);

        $countsBySlug = [];
        foreach ($report['detail'] as $line) {
            $countsBySlug[$line['catch_state']['slug']] = $line['count'];
        }
        $this->assertEquals(
            ['no' => $countNo, 'maybe' => $countMaybe, 'maybenot' => $countMaybeNot, 'yes' => $countYes],
            $countsBySlug
        );
    }

    private function assertReportShapeIsWellFormed(): void
    {
        $this->assertNotEmpty($this->lastResponseReportsBySlug);

        foreach ($this->lastResponseReportsBySlug as $rawReport) {
            /** @var array{total: int, total_caught: int, total_uncaught: int, detail: array<int, array{catch_state: array{slug: string}, count: int}>} $report */
            $report = $rawReport;
            $this->assertGreaterThanOrEqual(0, $report['total']);
            // A dex slug absent from AlbumReportService::getBatch()'s result falls back to an
            // empty default Report (TrainerDexResponseFactory::fromSqlRows()); every other dex
            // reports one count per catch state (no/maybe/maybenot/yes).
            $this->assertContains(\count($report['detail']), [0, 4]);
        }
    }
}
