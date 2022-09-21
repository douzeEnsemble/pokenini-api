<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Api;

class GameBundlesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[]|string[][] $content */
        $content = $this->apiRequestContent('game_bundles');

        $this->assertEquals([
            'name' => 'Red, Green, Blue, Yellow',
            'slug' => 'redgreenblueyellow',
            'generation' => [
                'name' => '1',
                'slug' => '1',
            ]
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Black 2, White 2',
            'slug' => 'black2white2',
            'generation' => [
                'name' => '5',
                'slug' => '5',
            ],
        ], $content[7]);

        $this->assertEquals([
            'name' => 'X, Y',
            'slug' => 'xy',
            'generation' => [
                'name' => '6',
                'slug' => '6',
            ],
        ], $content[8]);
    }
}
