<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\ActionLogsController;
use App\Service\ActionLogsService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogsController::class)]
#[CoversClass(ActionLogsService::class)]
class ActionLogsControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testActionLogs(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            'action_logs',
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        /** @var string[][][] $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertThereIsNoLast($data, 'calculate_dex_availabilities');
        $this->assertCurrentIsDone($data, 'calculate_dex_availabilities');

        $this->assertThereIsNoLast($data, 'calculate_game_bundles_availabilities');
        $this->assertCurrentIsNotDone($data, 'calculate_game_bundles_availabilities');

        $this->assertThereIsNoLast($data, 'calculate_game_bundles_shinies_availabilities');
        $this->assertCurrentIsFailed($data, 'calculate_game_bundles_shinies_availabilities');

        $this->assertLastIsDone($data, 'update_games_collections_and_dex');
        $this->assertCurrentIsNotDone($data, 'update_games_collections_and_dex');

        $this->assertLastIsDone($data, 'update_games_availabilities');
        $this->assertCurrentIsFailed($data, 'update_games_availabilities');

        $this->assertLastIsDone($data, 'update_labels');
        $this->assertCurrentIsDone($data, 'update_labels');

        $this->assertLastIsFailed($data, 'update_games_shinies_availabilities');
        $this->assertCurrentIsFailed($data, 'update_games_shinies_availabilities');

        $this->assertLastIsNotDone($data, 'update_pokemons');
        $this->assertCurrentIsDone($data, 'update_pokemons');

        $this->assertLastIsNotDone($data, 'update_regional_dex_numbers');
        $this->assertCurrentIsNotDone($data, 'update_regional_dex_numbers');
    }

    /**
     * @param string[][][] $data
     */
    private function assertThereIsNoLast(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayNotHasKey('last', $data[$key]);
    }

    /**
     * @param string[][][] $data
     */
    private function assertCurrentIsNotDone(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayHasKey('current', $data[$key]);
        $this->assertIsNotDone($data[$key]['current']);
    }

    /**
     * @param string[][][] $data
     */
    private function assertCurrentIsDone(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayHasKey('current', $data[$key]);
        $this->assertIsDone($data[$key]['current']);
    }

    /**
     * @param string[][][] $data
     */
    private function assertCurrentIsFailed(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayHasKey('current', $data[$key]);
        $this->assertIsFailed($data[$key]['current']);
    }

    /**
     * @param string[][][] $data
     */
    private function assertLastIsNotDone(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayHasKey('last', $data[$key]);
        $this->assertIsNotDone($data[$key]['last']);
    }

    /**
     * @param string[][][] $data
     */
    private function assertLastIsDone(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayHasKey('last', $data[$key]);
        $this->assertIsDone($data[$key]['last']);
    }

    /**
     * @param string[][][] $data
     */
    private function assertLastIsFailed(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertArrayHasKey('last', $data[$key]);
        $this->assertIsFailed($data[$key]['last']);
    }

    /**
     * @param null[]|string[] $data
     */
    private function assertIsNotDone(array $data): void
    {
        $this->assertArrayHasKey('created_at', $data);
        $this->assertIsString($data['created_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['created_at']
        );

        $this->assertArrayHasKey('done_at', $data);
        $this->assertNull($data['done_at']);

        $this->assertArrayHasKey('execution_time', $data);
        $this->assertNull($data['execution_time']);

        $this->assertArrayHasKey('details', $data);
        $this->assertNull($data['details']);

        $this->assertArrayHasKey('error_trace', $data);
        $this->assertNull($data['error_trace']);
    }

    /**
     * @param null[]|null[][]|string[]|string[][] $data
     */
    private function assertIsDone(array $data): void
    {
        $this->assertArrayHasKey('created_at', $data);
        $this->assertIsString($data['created_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['created_at']
        );

        $this->assertArrayHasKey('done_at', $data);
        $this->assertIsString($data['done_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['done_at']
        );

        $this->assertArrayHasKey('execution_time', $data);
        $this->assertIsString($data['execution_time']);
        $this->assertMatchesRegularExpression(
            '/^\d*$/',
            $data['execution_time']
        );

        $this->assertArrayHasKey('details', $data);
        $this->assertNotNull($data['details']);
        $this->assertIsArray($data['details']);

        $this->assertArrayHasKey('error_trace', $data);
        $this->assertNull($data['error_trace']);
    }

    /**
     * @param null[]|string[] $data
     */
    private function assertIsFailed(array $data): void
    {
        $this->assertArrayHasKey('created_at', $data);
        $this->assertIsString($data['created_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['created_at']
        );

        $this->assertArrayHasKey('done_at', $data);
        $this->assertIsString($data['done_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['done_at']
        );

        $this->assertArrayHasKey('execution_time', $data);
        $this->assertIsString($data['execution_time']);
        $this->assertMatchesRegularExpression(
            '/^\d*$/',
            $data['execution_time']
        );

        $this->assertArrayHasKey('details', $data);
        $this->assertNull($data['details']);

        $this->assertArrayHasKey('error_trace', $data);
        $this->assertNotEmpty($data['error_trace']);
    }
}
