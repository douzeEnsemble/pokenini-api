<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\PokemonsController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PokemonsController::class)]
final class PokemonsControllerTest extends AbstractTestControllerApi
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

        $this->assertJsonResponseIsOK();

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

        $this->assertJsonResponseIsOK();

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

        $this->assertJsonResponseIsOK();

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
                'PHP_AUTH_USER' => self::AUTH_USER,
                'PHP_AUTH_PW' => self::AUTH_PASSWORD,
            ],
        );

        $this->assertJsonResponseIsOK();

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
                'PHP_AUTH_USER' => self::AUTH_USER,
                'PHP_AUTH_PW' => 'treize',
            ],
        );

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }

    private function assertResponseContent(int $expectedCount): void
    {
        /** @var array<string, mixed> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('type', $content);
        $this->assertSame('pick', $content['type']);

        $this->assertArrayHasKey('items', $content);

        /** @var array<array<string, mixed>> $items */
        $items = $content['items'];
        $this->assertCount($expectedCount, $items);

        foreach ($items as $item) {
            $this->assertArrayHasKey('pokemon', $item);
            $this->assertIsArray($item['pokemon']);

            /** @var array<string, mixed> $pokemon */
            $pokemon = $item['pokemon'];
            $this->assertArrayHasKey('slug', $pokemon);
            $this->assertArrayHasKey('french_name', $pokemon);
            $this->assertArrayHasKey('icon', $pokemon);
            $this->assertArrayHasKey('national_dex_number', $pokemon);
            $this->assertArrayHasKey('order_number', $pokemon);
            $this->assertArrayHasKey('game_bundles', $pokemon);
            $this->assertArrayHasKey('game_bundles_shiny', $pokemon);
            $this->assertIsArray($pokemon['game_bundles']);
            $this->assertIsArray($pokemon['game_bundles_shiny']);

            $this->assertArrayHasKey('forms', $item);

            $this->assertArrayHasKey('types', $item);
            $this->assertIsArray($item['types']);

            /** @var array<string, mixed> $types */
            $types = $item['types'];
            $this->assertArrayHasKey('primary', $types);
            $this->assertArrayHasKey('secondary', $types);

            if (null !== $types['primary']) {
                $this->assertIsArray($types['primary']);

                /** @var array<string, mixed> $primary */
                $primary = $types['primary'];
                $this->assertArrayHasKey('slug', $primary);
                $this->assertArrayHasKey('name', $primary);
                $this->assertArrayHasKey('french_name', $primary);
                $this->assertArrayHasKey('color', $primary);
                $this->assertSame('', $primary['color']);
            }
        }
    }
}
