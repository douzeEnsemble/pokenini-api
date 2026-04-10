<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\PokemonsController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PokemonsController::class)]
class PokemonsControllerTest extends AbstractTestControllerApi
{
    public function testGetListFromDex(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'home',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ]
        );

        $this->assertResponseIsOK();

        $this->assertResponseContent(12);
    }

    public function testGetListFromDexBis(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'redgreenblueyellow',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
        );

        $this->assertResponseIsOK();

        $this->assertResponseContent(7);
    }

    public function testGetListFromDexTer(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'affinee',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
        );

        $this->assertResponseIsOK();

        $this->assertResponseContent(1);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'home',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'douze',
            ],
        );

        $this->assertResponseIsOK();

        $this->assertResponseContent(12);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest(
            'GET',
            '/pokemons/to_choose',
            [
                'count' => '12',
                'dex_slug' => 'home',
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
            ],
            [
                'PHP_AUTH_USER' => 'web',
                'PHP_AUTH_PW' => 'treize',
            ],
        );

        $this->assertEquals(401, $this->getResponse()->getStatusCode());
    }

    private function assertResponseContent(int $expectedCount): void
    {
        /** @var string[]|string[][][] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('type', $content);
        $this->assertSame('pick', $content['type']);

        $this->assertArrayHasKey('items', $content);

        /** @var string[][] $items */
        $items = $content['items'];
        $this->assertCount($expectedCount, $items);

        foreach ($items as $pokemon) {
            $this->assertArrayHasKey('pokemon_slug', $pokemon);
            $this->assertArrayHasKey('pokemon_french_name', $pokemon);
            $this->assertArrayHasKey('pokemon_icon', $pokemon);
        }
    }
}
