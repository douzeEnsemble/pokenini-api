<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\AdminCalculateController;
use App\Message\CalculateDexAvailabilities;
use App\Message\CalculateGameBundlesAvailabilities;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\Message\CalculatePokemonAvailabilities;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(AdminCalculateController::class)]
class AdminCalculateControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;

    public function testCalculateGameBundlesAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/game_bundles_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateGameBundlesAvailabilities::class, 1);
    }

    public function testCalculateGameBundlesShiniesAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/game_bundles_shinies_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateGameBundlesShiniesAvailabilities::class, 1);
    }

    public function testCalculateDexAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/dex_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateDexAvailabilities::class, 1);
    }

    public function testCalculatePokemonAvailabilities(): void
    {
        $client = static::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/pokemon_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculatePokemonAvailabilities::class, 1);
    }

    public function testUpdateBadAuth(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/istration/calculate/game_bundles_shinies_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'treize',
            ],
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
