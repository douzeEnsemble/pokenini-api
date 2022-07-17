<?php

namespace App\Tests\functionnal\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class GameAvailabilitiesApiTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request(
            'GET',
            '/game_availabilities',
            [
                'headers' => [
                    'accept' => 'application/json'
                ]
            ]
        );

        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'pokemon' => [
                'name' => 'Bulbasaur',
            ],
            'game' => [
                'name' => 'Red',
            ],
            'availability' => 'C',
        ], $content[0]);

        $this->assertEquals([
            'pokemon' => [
                'name' => 'Douze',
            ],
            'game' => [
                'name' => 'Green',
            ],
            'availability' => '',
        ], $content[7]);

        $this->assertEquals([
            'pokemon' => [
                'name' => 'Douze',
            ],
            'game' => [
                'name' => 'Blue',
            ],
            'availability' => 'E',
        ], $content[8]);
    }
}
