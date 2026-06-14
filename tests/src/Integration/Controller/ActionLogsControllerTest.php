<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ActionLogsController;
use App\Factory\ActionLogResponseFactory;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @psalm-type ActionLogEntry = array{
 *     created_at: string,
 *     done_at: string|null,
 *     execution_time: string|null,
 *     details: array<string, string>|null,
 *     error_trace: string|null
 * }
 * @psalm-type ActionLogData = list<array{action_type: string, current: ActionLogEntry|null, last: ActionLogEntry|null}>
 */
#[CoversClass(ActionLogsController::class)]
#[CoversClass(ActionLogResponseFactory::class)]
final class ActionLogsControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    #[Test]
    public function actionLogsReturnsExpectedStructure(): void
    {
        $client = self::createClient();

        $client->request(
            'GET',
            '/action_logs',
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        /** @var ActionLogData $data */
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
     * @param ActionLogData $data
     */
    private function assertThereIsNoLast(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertNull($entry['last']);
    }

    /**
     * @param ActionLogData $data
     */
    private function assertCurrentIsNotDone(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertIsNotDone($entry['current']);
    }

    /**
     * @param ActionLogData $data
     */
    private function assertCurrentIsDone(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertIsDone($entry['current']);
    }

    /**
     * @param ActionLogData $data
     */
    private function assertCurrentIsFailed(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertIsFailed($entry['current']);
    }

    /**
     * @param ActionLogData $data
     */
    private function assertLastIsNotDone(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertIsNotDone($entry['last']);
    }

    /**
     * @param ActionLogData $data
     */
    private function assertLastIsDone(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertIsDone($entry['last']);
    }

    /**
     * @param ActionLogData $data
     */
    private function assertLastIsFailed(array $data, string $key): void
    {
        $entry = $this->findByActionType($data, $key);
        $this->assertIsFailed($entry['last']);
    }

    /**
     * @param ActionLogData $data
     *
     * @return array{action_type: string, current: null|ActionLogEntry, last: null|ActionLogEntry}
     */
    private function findByActionType(array $data, string $actionType): array
    {
        foreach ($data as $entry) {
            if ($entry['action_type'] === $actionType) {
                return $entry;
            }
        }

        $this->fail(sprintf('No action log entry found with action_type "%s".', $actionType));
    }

    /**
     * @param null|ActionLogEntry $data
     */
    private function assertIsNotDone(?array $data): void
    {
        $this->assertNotNull($data);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['created_at']
        );
        $this->assertNull($data['done_at']);
        $this->assertNull($data['execution_time']);
        $this->assertNull($data['details']);
        $this->assertNull($data['error_trace']);
    }

    /**
     * @param null|ActionLogEntry $data
     */
    private function assertIsDone(?array $data): void
    {
        $this->assertNotNull($data);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['created_at']
        );
        $this->assertIsString($data['done_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['done_at']
        );
        $this->assertIsString($data['execution_time']);
        $this->assertMatchesRegularExpression('/^\d*$/', $data['execution_time']);
        $this->assertIsArray($data['details']);
        $this->assertNull($data['error_trace']);
    }

    /**
     * @param null|ActionLogEntry $data
     */
    private function assertIsFailed(?array $data): void
    {
        $this->assertNotNull($data);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['created_at']
        );
        $this->assertIsString($data['done_at']);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01]\d):[0-5]\d:[0-5]\d\+\d{2}$/',
            $data['done_at']
        );
        $this->assertIsString($data['execution_time']);
        $this->assertMatchesRegularExpression('/^\d*$/', $data['execution_time']);
        $this->assertNull($data['details']);
        $this->assertNotEmpty($data['error_trace']);
    }
}
