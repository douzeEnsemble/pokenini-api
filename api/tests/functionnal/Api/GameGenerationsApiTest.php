<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class GameGenerationsApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/game_generations',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        /** @var mixed[] $content */
        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => '1',
        ], $content[0]);

        $this->assertEquals([
            'name' => '2',
        ], $content[1]);

        $this->assertEquals([
            'name' => '8',
        ], $content[7]);
    }
}
