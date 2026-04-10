<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TrainerPokemonEloController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloController::class)]
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

        /** @var string[][] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content);

        foreach ($content as $pokemon) {
            $this->assertArrayHasKey('elo', $pokemon);
            $this->assertArrayHasKey('pokemon_slug', $pokemon);
            $this->assertArrayHasKey('pokemon_french_name', $pokemon);
            $this->assertArrayHasKey('pokemon_icon', $pokemon);
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

        /** @var string[][] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(5, $content);

        foreach ($content as $pokemon) {
            $this->assertArrayHasKey('elo', $pokemon);
            $this->assertArrayHasKey('pokemon_slug', $pokemon);
            $this->assertArrayHasKey('pokemon_french_name', $pokemon);
            $this->assertArrayHasKey('pokemon_icon', $pokemon);
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

        /** @var float[]|int[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
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

        /** @var float[]|int[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
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

        /** @var float[]|int[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertSame(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'treize',
            ]
        );

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
