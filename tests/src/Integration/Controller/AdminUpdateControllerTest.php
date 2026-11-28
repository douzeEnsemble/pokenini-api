<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AdminActionController;
use App\Message\UpdateCollectionsAvailabilities;
use App\Message\UpdateGamesAvailabilities;
use App\Message\UpdateGamesCollectionsAndDex;
use App\Message\UpdateGamesShiniesAvailabilities;
use App\Message\UpdateLabels;
use App\Message\UpdatePokemons;
use App\Message\UpdateRegionalDexNumbers;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(AdminActionController::class)]
final class AdminUpdateControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    #[Test]
    public function updateLabels(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/labels',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateLabels::class);
    }

    #[Test]
    public function updateGamesCollectionsAndDex(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/games_collections_and_dex',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateGamesCollectionsAndDex::class);
    }

    #[Test]
    public function updatePokemons(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/pokemons',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdatePokemons::class);
    }

    #[Test]
    public function updateGamesAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/games_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateGamesAvailabilities::class);
    }

    #[Test]
    public function updateGamesShiniesAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/games_shinies_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateGamesShiniesAvailabilities::class);
    }

    #[Test]
    public function updateCollectionsAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/collections_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateCollectionsAvailabilities::class);
    }

    #[Test]
    public function updateRegionalDexNumbers(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/regional_dex_numbers',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateRegionalDexNumbers::class);
    }

    #[Test]
    public function updateCollections(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

        $client->request(
            'POST',
            '/istration/update/collections_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        self::assertQueuedMessageCount(1, 'async', UpdateCollectionsAvailabilities::class);
    }

    #[Test]
    public function updateAlreadyRunning(): void
    {
        $client = self::createClient();
        // Keep the same kernel (and DB connection) so the second request sees the first one's ActionLog.
        $client->disableReboot();
        $server = [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ];

        $client->request('POST', '/istration/update/labels', [], [], $server);
        $this->assertResponseStatusCodeSame(201);

        $client->request('POST', '/istration/update/labels', [], [], $server);
        $this->assertResponseStatusCodeSame(409);

        self::assertQueuedMessageCount(1, 'async', UpdateLabels::class);
    }

    #[Test]
    public function unknownAction(): void
    {
        $client = self::createClient();

        $client->request('POST', '/istration/update/nope', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $this->assertResponseStatusCodeSame(404);
        self::assertQueuedMessageCount(0, 'async');
    }

    #[Test]
    public function listActions(): void
    {
        $client = self::createClient();

        $client->request('GET', '/istration/actions', [], [], [
            'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
            'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var list<array{type: string, name: string, depends_on: list<string>}> $actions */
        $actions = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertCount(11, $actions);
        $byName = array_column($actions, null, 'name');
        $this->assertSame(
            ['type' => 'update', 'name' => 'labels', 'depends_on' => []],
            $byName['labels'],
        );
        $this->assertSame(
            ['update:games_availabilities', 'update:collections_availabilities'],
            $byName['dex_availabilities']['depends_on'],
        );
    }

    #[Test]
    public function updateBadAuth(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/istration/update/labels',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => 'treize',
            ],
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
