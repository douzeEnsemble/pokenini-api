<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AdminUpdateController;
use App\Message\UpdateCollectionsAvailabilities;
use App\Message\UpdateGamesAvailabilities;
use App\Message\UpdateGamesCollectionsAndDex;
use App\Message\UpdateGamesShiniesAvailabilities;
use App\Message\UpdateLabels;
use App\Message\UpdatePokemons;
use App\Message\UpdateRegionalDexNumbers;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(AdminUpdateController::class)]
final class AdminUpdateControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;

    public function testUpdateLabels(): void
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

    public function testUpdateGamesCollectionsAndDex(): void
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

    public function testUpdatePokemons(): void
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

    public function testUpdateGamesAvailabilities(): void
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

    public function testUpdateGamesShiniesAvailabilities(): void
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

    public function testUpdateCollectionsAvailabilities(): void
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

    public function testUpdateRegionalDexNumbers(): void
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

    public function testUpdateCollections(): void
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

    public function testUpdateBadAuth(): void
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
