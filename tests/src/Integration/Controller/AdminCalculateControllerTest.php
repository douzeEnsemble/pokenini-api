<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AdminCalculateController;
use App\Message\CalculateDexAvailabilities;
use App\Message\CalculateGameBundlesAvailabilities;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\Message\CalculatePokemonAvailabilities;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(AdminCalculateController::class)]
final class AdminCalculateControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use InteractsWithMessenger;

    #[Test]
    public function calculateGameBundlesAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/game_bundles_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateGameBundlesAvailabilities::class, 1);
    }

    #[Test]
    public function calculateGameBundlesShiniesAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/game_bundles_shinies_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateGameBundlesShiniesAvailabilities::class, 1);
    }

    #[Test]
    public function calculateDexAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/dex_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculateDexAvailabilities::class, 1);
    }

    #[Test]
    public function calculatePokemonAvailabilities(): void
    {
        $client = self::createClient();

        $this->transport('async')->queue()->assertEmpty();

        $client->request(
            'POST',
            '/istration/calculate/pokemon_availabilities',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
        );

        $this->assertResponseStatusCodeSame(201);

        $this->transport('async')->queue()->assertContains(CalculatePokemonAvailabilities::class, 1);
    }

    #[Test]
    public function updateBadAuth(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/istration/calculate/game_bundles_shinies_availabilities',
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
