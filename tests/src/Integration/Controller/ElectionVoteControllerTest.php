<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionVoteController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteController::class)]
final class ElectionVoteControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testVote(): void
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
            '{"trainer_external_id": "12", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '12',
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [
                        'butterfree',
                    ],
                    'losers_slugs' => [
                        'caterpie',
                        'metapod',
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon_slug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon_slug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteBis(): void
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
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [
                        'butterfree',
                    ],
                    'losers_slugs' => [
                        'caterpie',
                        'metapod',
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon_slug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon_slug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteAllLosers(): void
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
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": [], "losers_slugs": ["caterpie", "metapod", "butterfree"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [],
                    'losers_slugs' => [
                        'caterpie',
                        'metapod',
                        'butterfree',
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [],
                    'losers' => [
                        [
                            'pokemon_slug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
                            'elo' => 984,
                        ],
                        [
                            'pokemon_slug' => 'butterfree',
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteAllWinners(): void
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
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["caterpie", "metapod", "butterfree"], "losers_slugs": []}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dex_slug' => 'demo',
                    'election_slug' => '',
                    'winners_slugs' => [
                        'caterpie',
                        'metapod',
                        'butterfree',
                    ],
                    'losers_slugs' => [],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon_slug' => 'caterpie',
                            'elo' => 1016,
                        ],
                        [
                            'pokemon_slug' => 'metapod',
                            'elo' => 1016,
                        ],
                        [
                            'pokemon_slug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [],
                ],
            ],
            $data,
        );
    }

    public function testEmptyData(): void
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

    public function testEmptyDataBis(): void
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

    public function testBadVote(): void
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
