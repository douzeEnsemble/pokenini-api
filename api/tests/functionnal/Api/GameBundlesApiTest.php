<?php

namespace App\Tests\Functionnal\Api;

class GameBundlesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('game_bundles');

        $this->assertEquals([
            'name' => 'Red, Green, Blue, Yellow',
            'generation' => [
                'name' => '1',
            ]
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Black 2, White 2',
            'generation' => [
                'name' => '5',
            ],
        ], $content[7]);

        $this->assertEquals([
            'name' => 'X, Y',
            'generation' => [
                'name' => '6',
            ],
        ], $content[8]);
    }
}
