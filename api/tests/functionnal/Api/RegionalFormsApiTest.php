<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class RegionalFormsApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/regional_forms',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        /** @var mixed[] $content */
        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => 'Alolan',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Galarian',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Hisuian',
        ], $content[2]);
    }
}
