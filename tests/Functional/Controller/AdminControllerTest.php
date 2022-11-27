<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Common\Traits\CounterTrait\CounterTableTrait;
use Doctrine\DBAL\Connection;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use CounterTableTrait;

    public function testUpdateLabels(): void
    {
        $client = static::createClient();

        $this->assertEquals(5, $this->getTableCount('catch_state'));
        $this->assertEquals(3, $this->getTableCount('category_form'));
        $this->assertEquals(3, $this->getTableCount('regional_form'));
        $this->assertEquals(3, $this->getTableCount('special_form'));
        $this->assertEquals(7, $this->getTableCount('variant_form'));

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

        $this->assertResponseIsSuccessful();

        $this->assertEquals(9, $this->getTableCount('catch_state'));
        $this->assertEquals(4, $this->getTableCount('category_form'));
        $this->assertEquals(4, $this->getTableCount('regional_form'));
        $this->assertEquals(5, $this->getTableCount('special_form'));
        $this->assertEquals(8, $this->getTableCount('variant_form'));
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

    public function testUpdateGamesAndDexes(): void
    {
        $client = static::createClient();

        $this->assertEquals(8, $this->getTableCount('game_generation'));
        $this->assertEquals(16, $this->getTableCount('game_bundle'));
        $this->assertEquals(36, $this->getTableCount('game'));
        $this->assertEquals(6, $this->getTableCount('dex'));

        $client->request(
            'POST',
            "/istration/update/games_and_dexes",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $this->assertEquals(9, $this->getTableCount('game_generation'));
        $this->assertEquals(17, $this->getTableCount('game_bundle'));
        $this->assertEquals(38, $this->getTableCount('game'));
        $this->assertEquals(22, $this->getTableCount('dex'));
    }

    public function testUpdatePokemons(): void
    {
        $client = static::createClient();

        $this->assertEquals(19, $this->getTableCount('pokemon'));

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

        $this->assertResponseIsSuccessful();

        $this->assertEquals(1816, $this->getTableCount('pokemon'));
    }

    public function testUpdateGameAvailability(): void
    {
        $client = static::createClient();

        $this->assertEquals(23, $this->getTableCount('game_availability'));

        $client->request(
            'POST',
            "/istration/update/game_availability",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $this->assertEquals(7980, $this->getTableCount('game_availability'));
    }

    public function testUpdateRegionalDexNumber(): void
    {
        $client = static::createClient();

        $this->assertEquals(12, $this->getTableCount('regional_dex_number'));

        $client->request(
            'POST',
            "/istration/update/regional_dex_number",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $this->assertEquals(2863, $this->getTableCount('regional_dex_number'));
    }

    public function testCalculateGameBundleAvailability(): void
    {
        $client = static::createClient();

        $this->assertEquals(22, $this->getTableCount('game_bundle_availability'));

        $client->request(
            'POST',
            "/istration/calculate/game_bundle_availability",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $this->assertEquals(18, $this->getTableCount('game_bundle_availability'));
    }

    public function testCalculateDexAvailability(): void
    {
        $client = static::createClient();

        $this->assertEquals(39, $this->getTableCount('dex_availability'));

        $client->request(
            'POST',
            "/istration/calculate/dex_availability",
            [],
            [],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW'   => 'douze',
            ],
        );

        $this->assertResponseIsSuccessful();

        $this->assertEquals(61, $this->getTableCount('dex_availability'));
    }
}
