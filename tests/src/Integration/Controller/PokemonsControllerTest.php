<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\PokemonsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(PokemonsController::class)]
final class PokemonsControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function getListFromDex(): void
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

    #[Test]
    public function getListFromDexBis(): void
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

    #[Test]
    public function getListFromDexTer(): void
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

    #[Test]
    public function getAuth(): void
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

    #[Test]
    public function getBadAuth(): void
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
            $this->assertArrayHasKey('labels', $pokemon);
            $this->assertArrayHasKey('icon', $pokemon);
            $this->assertArrayHasKey('national_dex_number', $pokemon);
            $this->assertArrayHasKey('order_number', $pokemon);
            $this->assertArrayHasKey('game_bundles', $pokemon);
            $this->assertIsArray($pokemon['game_bundles']);

            /** @var array<string, mixed> $gameBundles */
            $gameBundles = $pokemon['game_bundles'];
            $this->assertArrayHasKey('normal', $gameBundles);
            $this->assertArrayHasKey('shiny', $gameBundles);
            $this->assertIsArray($gameBundles['normal']);
            $this->assertIsArray($gameBundles['shiny']);

            /** @var array<string, mixed> $gameBundle */
            foreach ($gameBundles['normal'] as $gameBundle) {
                $this->assertArrayHasKey('slug', $gameBundle);
                $this->assertIsString($gameBundle['slug']);
            }

            /** @var array<string, mixed> $gameBundleShiny */
            foreach ($gameBundles['shiny'] as $gameBundleShiny) {
                $this->assertArrayHasKey('slug', $gameBundleShiny);
                $this->assertIsString($gameBundleShiny['slug']);
            }

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
                $this->assertIsString($primary['color']);
                $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $primary['color']);
            }
        }
    }
}
