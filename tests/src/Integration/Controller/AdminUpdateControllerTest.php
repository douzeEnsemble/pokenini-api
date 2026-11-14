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
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(AdminActionController::class)]
final class AdminUpdateControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;

    #[Test]
    public function updateLabels(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateLabels::class, 1);
    }

    #[Test]
    public function updateGamesCollectionsAndDex(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateGamesCollectionsAndDex::class, 1);
    }

    #[Test]
    public function updatePokemons(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdatePokemons::class, 1);
    }

    #[Test]
    public function updateGamesAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateGamesAvailabilities::class, 1);
    }

    #[Test]
    public function updateGamesShiniesAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateGamesShiniesAvailabilities::class, 1);
    }

    #[Test]
    public function updateCollectionsAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateCollectionsAvailabilities::class, 1);
    }

    #[Test]
    public function updateRegionalDexNumbers(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateRegionalDexNumbers::class, 1);
    }

    #[Test]
    public function updateCollections(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

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

        $this->transport('async')->queue()->assertContains(UpdateCollectionsAvailabilities::class, 1);
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

        $this->transport('async')->queue()->assertContains(UpdateLabels::class, 1);
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
        $this->transport('async')->queue()->assertEmpty();
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
