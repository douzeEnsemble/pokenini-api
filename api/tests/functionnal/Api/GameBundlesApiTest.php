<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class GameBundlesApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/game_bundles',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        /** @var mixed[] $content */
        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => 'Red, Green, Blue, Yellow',
            'generation' => [
                'name' => '1',
            ]
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Black 2, White 2',
            'generation' => [
                'name' => '5',
            ],
        ], $content[7]);

        $this->assertEquals([
            'name' => 'X, Y',
            'generation' => [
                'name' => '6',
            ],
        ], $content[8]);
    }
}
