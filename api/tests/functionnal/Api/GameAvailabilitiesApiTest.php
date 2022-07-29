<?php

namespace App\Tests\Functionnal\Api;

class GameAvailabilitiesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('game_availabilities');

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
                'name' => 'Yellow',
            ],
            'availability' => '—',
        ], $content[21]);

        $this->assertEquals([
            'pokemon' => [
                'name' => 'Douze',
            ],
            'game' => [
                'name' => 'Silver',
            ],
            'availability' => '',
        ], $content[22]);
    }
}
