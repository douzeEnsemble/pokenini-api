<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class SpecialFormsApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/special_forms',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => 'Mega',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gigantamax',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Alpha',
        ], $content[2]);
    }
}
