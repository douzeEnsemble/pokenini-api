<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Message\CalculateDexAvailabilities;
use App\Message\CalculateGameBundlesAvailabilities;
use App\Message\UpdateGamesAndDex;
use App\Message\UpdateGamesAvailabilities;
use App\Message\UpdateGamesShiniesAvailabilities;
use App\Message\UpdateLabels;
use App\Message\UpdatePokemons;
use App\Message\UpdateRegionalDexNumbers;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class AdminControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;

    public function testUpdateLabels(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/update/labels",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(UpdateLabels::class, 1);
    }

    public function testUpdateGamesAndDex(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/update/games_and_dex",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(UpdateGamesAndDex::class, 1);
    }

    public function testUpdatePokemons(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/update/pokemons",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(UpdatePokemons::class, 1);
    }

    public function testUpdateGamesAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/update/games_availabilities",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(UpdateGamesAvailabilities::class, 1);
    }

    public function testUpdateGamesShiniesAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/update/games_shinies_availabilities",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(UpdateGamesShiniesAvailabilities::class, 1);
    }

    public function testUpdateRegionalDexNumbers(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/update/regional_dex_numbers",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(UpdateRegionalDexNumbers::class, 1);
    }

    public function testCalculateGameBundlesAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/calculate/game_bundles_availabilities",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateGameBundlesAvailabilities::class, 1);
    }

    public function testCalculateDexAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            "/istration/calculate/dex_availabilities",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateDexAvailabilities::class, 1);
    }

    public function testUpdateBadAuth(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            "/istration/update/labels",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'treize',
            ],
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
