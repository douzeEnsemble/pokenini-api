<?php

namespace App\Tests\functionnal\Api;

class VariantFormsApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('variant_forms');

        $this->assertEquals([
            'name' => 'Gender',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Alternate',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Fusion',
        ], $content[5]);
    }
}
