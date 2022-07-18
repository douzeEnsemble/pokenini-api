<?php

namespace App\Tests\functionnal\Api;

class CatchStatesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('catch_states');

        $this->assertEquals([
            'name' => 'No',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Maybe',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Maybe not',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Yes',
        ], $content[3]);
    }
}
