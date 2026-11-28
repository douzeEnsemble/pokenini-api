<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\AdminActionController;
use App\Message\CalculateDexAvailabilities;
use App\Message\CalculateGameBundlesAvailabilities;
use App\Message\CalculateGameBundlesShiniesAvailabilities;
use App\Message\CalculatePokemonAvailabilities;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(AdminActionController::class)]
final class AdminCalculateControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    #[Test]
    public function calculateGameBundlesAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

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

        self::assertQueuedMessageCount(1, 'async', CalculateGameBundlesAvailabilities::class);
    }

    #[Test]
    public function calculateGameBundlesShiniesAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

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

        self::assertQueuedMessageCount(1, 'async', CalculateGameBundlesShiniesAvailabilities::class);
    }

    #[Test]
    public function calculateDexAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

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

        self::assertQueuedMessageCount(1, 'async', CalculateDexAvailabilities::class);
    }

    #[Test]
    public function calculatePokemonAvailabilities(): void
    {
        $client = self::createClient();

        self::assertQueuedMessageCount(0, 'async');

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

        self::assertQueuedMessageCount(1, 'async', CalculatePokemonAvailabilities::class);
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
