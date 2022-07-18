<?php

namespace App\Tests\functionnal\Api;

class RegionalFormsApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('regional_forms');

        $this->assertEquals([
            'name' => 'Alolan',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Galarian',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Hisuian',
        ], $content[2]);
    }
}
