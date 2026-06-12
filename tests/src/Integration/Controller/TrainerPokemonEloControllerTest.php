<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPokemonEloController;
use App\DTO\Response\ElectionViewCountResponse;
use App\DTO\Response\ElectionWinCountResponse;
use App\Factory\ElectionMetricsResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloController::class)]
#[CoversClass(ElectionMetricsResponseFactory::class)]
#[CoversClass(ElectionViewCountResponse::class)]
#[CoversClass(ElectionWinCountResponse::class)]
final class TrainerPokemonEloControllerTest extends AbstractTestControllerApi
{
    public function testGetTop(): void
    {
        $this->apiRequest(
            'GET',
            '/election/top',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'home',
                'election_slug' => 'favorite',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array<int, array<string, mixed>> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content);

        foreach ($content as $item) {
            $this->assertArrayHasKey('elo', $item);
            $this->assertArrayHasKey('significance', $item);
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertArrayHasKey('forms', $item);
            $this->assertArrayHasKey('types', $item);

            $this->assertIsFloat($item['elo']);
            $this->assertIsBool($item['significance']);

            $pokemon = $item['pokemon'];
            $this->assertIsArray($pokemon);
            $this->assertArrayHasKey('slug', $pokemon);
            $this->assertArrayHasKey('name', $pokemon);
            $this->assertArrayHasKey('french_name', $pokemon);
            $this->assertArrayHasKey('national_dex_number', $pokemon);
            $this->assertArrayHasKey('icon', $pokemon);
            $this->assertArrayHasKey('family_order', $pokemon);
            $this->assertArrayHasKey('family_lead', $pokemon);
            $this->assertArrayHasKey('original_game_bundle', $pokemon);
            $this->assertArrayHasKey('order_number', $pokemon);

            $types = $item['types'];
            $this->assertIsArray($types);
            $this->assertArrayHasKey('primary', $types);
            $this->assertIsArray($types['primary']);
            $this->assertArrayHasKey('slug', $types['primary']);
            $this->assertArrayHasKey('name', $types['primary']);
            $this->assertArrayHasKey('french_name', $types['primary']);
            $this->assertArrayHasKey('color', $types['primary']);
            $this->assertIsString($types['primary']['color']);
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $types['primary']['color']);
        }
    }

    public function testGetTopBis(): void
    {
        $this->apiRequest(
            'GET',
            '/election/top',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'demo',
                'election_slug' => '',
                'count' => '5',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array<int, array<string, mixed>> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content);

        foreach ($content as $item) {
            $this->assertArrayHasKey('elo', $item);
            $this->assertArrayHasKey('significance', $item);
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertArrayHasKey('forms', $item);
            $this->assertArrayHasKey('types', $item);

            $pokemon = $item['pokemon'];
            $this->assertIsArray($pokemon);
            $this->assertArrayHasKey('slug', $pokemon);
            $this->assertArrayHasKey('name', $pokemon);
            $this->assertArrayHasKey('french_name', $pokemon);
            $this->assertArrayHasKey('icon', $pokemon);
            $this->assertIsString($pokemon['slug']);
            $this->assertIsString($pokemon['french_name']);
        }
    }

    public function testGetMetrics(): void
    {
        $this->apiRequest(
            'GET',
            '/election/metrics',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'demo',
                'election_slug' => '',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array<string, array<string, int>|int> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count' => ['sum' => 0, 'max' => 0],
                'win_count' => ['sum' => 0, 'max' => 0],
                'under_max_view_count' => 15,
                'max_view_count' => 15,
                'dex_total_count' => 21,
            ],
            $content,
        );
    }

    public function testGetMetricsBis(): void
    {
        $this->apiRequest(
            'GET',
            '/election/metrics',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'affinee',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array<string, array<string, int>|int> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count' => ['sum' => 9, 'max' => 3],
                'win_count' => ['sum' => 6, 'max' => 3],
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
            $content,
        );
    }

    public function testGetMetricsNo(): void
    {
        $this->apiRequest(
            'GET',
            '/election/metrics',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'doesntexists',
            ]
        );

        $this->assertResponseIsOK();

        /** @var array<string, array<string, int>|int> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count' => ['sum' => 0, 'max' => 0],
                'win_count' => ['sum' => 0, 'max' => 0],
                'under_max_view_count' => 7,
                'max_view_count' => 0,
                'dex_total_count' => 7,
            ],
            $content,
        );
    }

    public function testGetAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/election/top',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'home',
                'election_slug' => 'favorite',
                'count' => '5',
            ],
            [
                'PHP_AUTH_USER' => self::AUTH_USER,
                'PHP_AUTH_PW' => self::AUTH_PASSWORD,
            ]
        );

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/election/top',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'home',
                'election_slug' => 'favorite',
                'count' => '5',
            ],
            [
                'PHP_AUTH_USER' => self::AUTH_USER,
                'PHP_AUTH_PW' => 'treize',
            ]
        );

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }

    public function testGetMetricsMatchesFixture(): void
    {
        $this->apiRequest(
            'GET',
            '/election/metrics',
            [
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'demo',
                'election_slug' => '',
            ]
        );

        $this->assertResponseIsOK();

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/election_metrics_response.json',
            $this->getClientResponseContent(),
        );
    }
}
