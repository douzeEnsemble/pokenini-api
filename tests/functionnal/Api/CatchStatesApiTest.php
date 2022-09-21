<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Api;

class CatchStatesApiTest extends AbstractApiTest
{
    public function testGetCollection(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('catch_states');

        $this->assertEquals([
            'name' => 'No',
            'frenchName' => 'Non',
            'slug' => 'no',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Maybe',
            'frenchName' => 'Peut être',
            'slug' => 'maybe',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Maybe not',
            'frenchName' => 'Peut être pas',
            'slug' => 'maybenot',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Yes',
            'frenchName' => 'Oui',
            'slug' => 'yes',
        ], $content[3]);
    }
}
