<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class VariantFormsApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/variant_forms',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        /** @var mixed[] $content */
        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'name' => 'Gender',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Alternate',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Fusion',
        ], $content[5]);
    }
}
