<?php

namespace App\Tests\functionnal\Api;

class GameGenerationsApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('game_generations');

        $this->assertEquals([
            'name' => '1',
        ], $content[0]);

        $this->assertEquals([
            'name' => '2',
        ], $content[1]);

        $this->assertEquals([
            'name' => '8',
        ], $content[7]);
    }
}
