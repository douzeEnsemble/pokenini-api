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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
            '{"trainer_external_id": "12", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'electionVote' => [
                    'trainerExternalId' => '12',
                    'dexSlug' => 'demo',
                    'electionSlug' => '',
                    'winnersSlugs' => [
                        'butterfree',
                    ],
                    'losersSlugs' => [
                        'caterpie',
                        'metapod',
                    ],
                ],
                'pokemonsElo' => [
                    'winners' => [
                        [
                            'pokemonSlug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemonSlug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemonSlug' => 'metapod',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'electionVote' => [
                    'trainerExternalId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dexSlug' => 'demo',
                    'electionSlug' => '',
                    'winnersSlugs' => [
                        'butterfree',
                    ],
                    'losersSlugs' => [
                        'caterpie',
                        'metapod',
                    ],
                ],
                'pokemonsElo' => [
                    'winners' => [
                        [
                            'pokemonSlug' => 'butterfree',
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemonSlug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemonSlug' => 'metapod',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": [], "losers_slugs": ["caterpie", "metapod", "butterfree"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'electionVote' => [
                    'trainerExternalId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dexSlug' => 'demo',
                    'electionSlug' => '',
                    'winnersSlugs' => [],
                    'losersSlugs' => [
                        'caterpie',
                        'metapod',
                        'butterfree',
                    ],
                ],
                'pokemonsElo' => [
                    'winners' => [],
                    'losers' => [
                        [
                            'pokemonSlug' => 'caterpie',
                            'elo' => 984,
                        ],
                        [
                            'pokemonSlug' => 'metapod',
                            'elo' => 984,
                        ],
                        [
                            'pokemonSlug' => 'butterfree',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
            '{"trainer_external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554", "dex_slug": "demo", "election_slug": "", "winners_slugs": ["caterpie", "metapod", "butterfree"], "losers_slugs": []}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'electionVote' => [
                    'trainerExternalId' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    'dexSlug' => 'demo',
                    'electionSlug' => '',
                    'winnersSlugs' => [
                        'caterpie',
                        'metapod',
                        'butterfree',
                    ],
                    'losersSlugs' => [],
                ],
                'pokemonsElo' => [
                    'winners' => [
                        [
                            'pokemonSlug' => 'caterpie',
                            'elo' => 1016,
                        ],
                        [
                            'pokemonSlug' => 'metapod',
                            'elo' => 1016,
                        ],
                        [
                            'pokemonSlug' => 'butterfree',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
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
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
            '{"trainerExternalId": "12", "dex_slug": "demo", "electionSlug": "", "winnersSlugs": "pichu", "losersSlugs": ["pikachu", "raichu"]}',
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
