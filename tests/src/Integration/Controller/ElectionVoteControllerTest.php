<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionVoteController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteController::class)]
final class ElectionVoteControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    #[Test]
    public function vote(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "12"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '12'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[Test]
    public function voteBis(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[Test]
    public function voteAllLosers(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": [], "losers_slugs": ["caterpie", "metapod", "butterfree"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                        ['slug' => 'butterfree'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    #[Test]
    public function voteAllWinners(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": ["caterpie", "metapod", "butterfree"], "losers_slugs": []}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 1016,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 1016,
                        ],
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [],
                ],
            ],
            $data,
        );
    }

    #[Test]
    public function emptyData(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function emptyDataBis(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{}',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function badVote(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainerExternalId": "12", "dex_slug": "demo", "electionSlug": "", "winnersSlugs": "pichu", "losersSlugs": ["pikachu", "raichu"]}',
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
