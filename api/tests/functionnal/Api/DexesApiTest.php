<?php

namespace App\Tests\functionnal\Api;

class DexesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('dexes');

        $this->assertEquals([
            'name' => 'Red / Blue / Green / Yellow',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gold, Silver, Crystal',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Home',
        ], $content[2]);
    }
}
