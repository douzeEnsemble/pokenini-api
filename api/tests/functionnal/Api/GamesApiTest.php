<?php

namespace App\Tests\Functionnal\\Api;

class GamesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[]|string[][]|string[][][] $content */
        $content = $this->apiRequestContent('games');

        $this->assertEquals([
            'bundle' => [
                'name' => 'Black 2, White 2',
                'generation' => [
                    'name' => '5',
                ]
            ],
            'name' => 'Black 2',
        ], $content[19]);

        $this->assertEquals([
            'bundle' => [
                'name' => 'Ultra Sun, Ultra Moon',
                'generation' => [
                    'name' => '7',
                ]
            ],
            'name' => 'Ultra Moon',
        ], $content[28]);

        $this->assertEquals([
            'bundle' => [
                'name' => 'Brilland Diamond, Shining Pearl',
                'generation' => [
                    'name' => '8',
                ]
            ],
            'name' => 'Shining Pearl',
        ], $content[34]);
    }
}
