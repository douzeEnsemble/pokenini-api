<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class DexesApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/dexes',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => 'Red / Blue / Green / Yellow',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gold, Silver, Crystal',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Home',
        ], $content[2]);
    }
}
