<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class CatchStatesApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/catch_states',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => 'No',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Maybe',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Maybe not',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Yes',
        ], $content[3]);
    }
}
