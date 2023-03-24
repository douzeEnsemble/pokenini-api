<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ActionLogsControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testActionLogs(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/action_logs',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();
        /** @var string[][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsDone($data, 'calculate_dex_availabilities');
        $this->assertIsNotDone($data, 'calculate_game_bundles_availabilities');
        $this->assertIsDone($data, 'update_games_and_dex');
        $this->assertIsDone($data, 'update_games_availabilities');
        $this->assertIsDone($data, 'update_labels');
        $this->assertIsDone($data, 'update_pokemons');
        $this->assertIsNotDone($data, 'update_regional_dex_numbers');
    }

    /**
     * @param string[][] $data
     */
    private function assertIsNotDone(array $data, string $key): void
    {
        $this->assertArrayHasKey('created_at', $data[$key]);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d$/',
            $data[$key]['created_at']
        );
        $this->assertArrayHasKey('done_at', $data[$key]);
        $this->assertNull($data[$key]['done_at']);

        $this->assertArrayHasKey('details', $data[$key]);
        $this->assertNull($data[$key]['details']);
    }

    /**
     * @param string[][] $data
     */
    private function assertIsDone(array $data, string $key): void
    {
        $this->assertArrayHasKey('created_at', $data[$key]);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d$/',
            $data[$key]['created_at']
        );

        $this->assertArrayHasKey('done_at', $data[$key]);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d$/',
            $data[$key]['done_at']
        );

        $this->assertArrayHasKey('details', $data[$key]);
        $this->assertNotNull($data[$key]['details']);
        $this->assertIsArray($data[$key]['details']);
    }
}
