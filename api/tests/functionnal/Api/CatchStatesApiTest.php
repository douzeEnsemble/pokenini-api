<?php

namespace App\Tests\Functionnal\Api;

class CatchStatesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('catch_states');

        $this->assertEquals([
            'name' => 'No',
            'slug' => 'no',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Maybe',
            'slug' => 'maybe',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Maybe not',
            'slug' => 'maybenot',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Yes',
            'slug' => 'yes',
        ], $content[3]);
    }
}
