<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class GamesApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/games',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        /** @var mixed[] $content */
        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'bundle' => [
                'name' => 'Black 2, White 2',
                'generation' => [
                    'name' => '5',
                ]
            ],
            'name' => 'Black 2',
        ], $content[19]);

        $this->assertEquals([
            'bundle' => [
                'name' => 'Ultra Sun, Ultra Moon',
                'generation' => [
                    'name' => '7',
                ]
            ],
            'name' => 'Ultra Moon',
        ], $content[28]);

        $this->assertEquals([
            'bundle' => [
                'name' => 'Brilland Diamond, Shining Pearl',
                'generation' => [
                    'name' => '8',
                ]
            ],
            'name' => 'Shining Pearl',
        ], $content[34]);
    }
}
