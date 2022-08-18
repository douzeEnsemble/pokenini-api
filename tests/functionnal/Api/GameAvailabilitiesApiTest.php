<?php

namespace App\Tests\Functionnal\Api;

class GameAvailabilitiesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('game_availabilities');

        $this->assertEquals([
            'pokemonName' => 'Bulbasaur',
            'game' => [
                'name' => 'Red',
            ],
            'availability' => 'C',
        ], $content[0]);

        $this->assertEquals([
            'pokemonName' => 'Douze',
            'game' => [
                'name' => 'Gold',
            ],
            'availability' => '',
        ], $content[12]);

        $this->assertEquals([
            'pokemonName' => 'Douze',
            'game' => [
                'name' => 'Yellow',
            ],
            'availability' => '—',
        ], $content[13]);
    }
}
