<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

class SpecialFormsApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('special_forms');

        $this->assertEquals([
            'name' => 'Mega',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Gigantamax',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Alpha',
        ], $content[2]);
    }
}
