<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionReportController;
use App\DTO\Response\ElectionEloScoreResponse;
use App\DTO\Response\ElectionMetricsCompletionResponse;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use App\Factory\DexResponseFactory;
use App\Factory\ElectionEloResponseFactory;
use App\Factory\ElectionMetricsResponseFactory;
use App\Factory\ElectionReportResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ElectionReportController::class)]
#[CoversClass(ElectionReportResponseFactory::class)]
#[CoversClass(DexResponseFactory::class)]
#[CoversClass(ElectionEloResponseFactory::class)]
#[CoversClass(ElectionMetricsResponseFactory::class)]
#[CoversClass(ElectionMetricsCompletionResponse::class)]
#[CoversClass(ElectionViewCountResponse::class)]
#[CoversClass(ElectionEloScoreResponse::class)]
#[CoversClass(ElectionWinCountResponse::class)]
final class ElectionReportControllerTest extends AbstractTestControllerApi
{
    private const string TRAINER_U12 = '7b52009b64fd0a2a49e6d8a939753077792b0554';

    public function testShowReturnsTopAndMetricsForHomeFavorite(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/home',
            [
                'election_slug' => 'favorite',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('top', $content);
        $this->assertArrayHasKey('metrics', $content);
        $this->assertCount(5, $content['top']);

        foreach ($content['top'] as $item) {
            $this->assertArrayHasKey('score', $item);
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertArrayHasKey('forms', $item);
            $this->assertArrayHasKey('types', $item);
        }
    }

    public function testShowReturnsMetricsForDemoWithDefaultElectionSlug(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/demo',
            [
                'election_slug' => '',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, array<string, int>|int>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content['top']);
        $this->assertSame(
            [
                'view_count' => ['sum' => 0, 'max' => 0],
                'win_count' => ['sum' => 0, 'max' => 0],
                'completion' => ['at_max_count' => 15, 'under_max_count' => 15],
                'dex_total_count' => 21,
            ],
            $content['metrics'],
        );
    }

    public function testShowReturnsMetricsForAffineeElection(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/redgreenblueyellow',
            [
                'election_slug' => 'affinee',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, array<string, int>|int>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count' => ['sum' => 9, 'max' => 3],
                'win_count' => ['sum' => 6, 'max' => 3],
                'completion' => ['at_max_count' => 1, 'under_max_count' => 1],
                'dex_total_count' => 7,
            ],
            $content['metrics'],
        );
    }

    public function testShowDefaultsElectionSlugAndCountWhenOmitted(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/demo');

        $this->assertResponseIsOK();

        /** @var array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>} $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content['top']);
    }

    public function testShowWithAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/home',
            ['election_slug' => 'favorite', 'count' => '5'],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]
        );

        $this->assertResponseIsOK();
    }

    public function testShowWithBadAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/election/'.self::TRAINER_U12.'/home',
            ['election_slug' => 'favorite', 'count' => '5'],
            ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']
        );

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }

    public function testListReturnsOneDexByDefault(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/list');

        $this->assertResponseIsOK();

        /** @var array<int, array{slug: string, report: array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>}}> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(1, $content);
        $this->assertSame('home', $content[0]['slug']);
        $this->assertArrayHasKey('report', $content[0]);
        $this->assertArrayHasKey('top', $content[0]['report']);
        $this->assertArrayHasKey('metrics', $content[0]['report']);
    }

    public function testListReturnsAllEligibleDexWithOptions(): void
    {
        // 'favorite' is the election_slug used across fixtures for 'home' and
        // 'redgreenblueyellow' (see fixtures/trainer_pokemon_elo.yaml); it's passed
        // explicitly so those two dex have real top-N data to assert on below.
        // 'homepogo' and 'spoon' are intentionally-empty fixture dex (zero rows in
        // dex_availability) used only to exercise the unreleased/premium eligibility
        // flags, so their top is always empty regardless of election_slug or count.
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/list', [
            'include_unreleased_dex' => '1',
            'include_premium_dex' => '1',
            'election_slug' => 'favorite',
            'count' => '1',
        ]);

        $this->assertResponseIsOK();

        /** @var array<int, array{slug: string, report: array{top: array<int, array<string, mixed>>, metrics: array<string, mixed>}}> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);
        $this->assertSame(
            ['homepogo', 'home', 'redgreenblueyellow', 'spoon'],
            array_map(static fn (array $item): string => (string) $item['slug'], $content),
        );

        foreach ($content as $item) {
            $this->assertLessThanOrEqual(1, \count($item['report']['top']));
            $this->assertArrayHasKey('dex_total_count', $item['report']['metrics']);
        }

        $bySlug = array_column($content, null, 'slug');
        $this->assertCount(1, $bySlug['home']['report']['top']);
        $this->assertCount(1, $bySlug['redgreenblueyellow']['report']['top']);
    }

    public function testListIsNotShadowedByTheSingleDexRoute(): void
    {
        $this->apiRequest('GET', '/election/'.self::TRAINER_U12.'/list');

        $this->assertResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertIsArray($content);
        $this->assertArrayNotHasKey('top', $content);
        $this->assertArrayNotHasKey('metrics', $content);
    }
}
