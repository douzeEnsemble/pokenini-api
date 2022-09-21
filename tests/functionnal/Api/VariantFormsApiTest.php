<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Api;

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
