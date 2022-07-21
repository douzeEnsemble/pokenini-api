<?php

namespace App\Tests\Functionnal\\Api;

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
