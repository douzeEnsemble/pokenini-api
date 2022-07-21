<?php

namespace App\Tests\Functionnal\Api;

class DexesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('dexes');

        $this->assertEquals([
            'name' => 'Red / Blue / Green / Yellow',
            'slug' => 'redbluegreenyellow',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gold, Silver, Crystal',
            'slug' => 'goldsilvercrystal',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Home',
            'slug' => 'home',
        ], $content[2]);
    }
}
