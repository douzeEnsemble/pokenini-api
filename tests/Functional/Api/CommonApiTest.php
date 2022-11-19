<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Component\HttpClient\Exception\ClientException;

class CommonApiTest extends AbstractApiTest
{
    public function testGetAuth(): void
    {
        /** @var string[] $content */
        $content = $this->apiRequestContent('catch_states', [], 'GET', ['auth_basic' => ['web', 'douze']]);

        $this->assertCount(4, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->expectException(ClientException::class);

        $this->apiRequestContent('catch_states', [], 'GET', ['auth_basic' => ['web', 'treize']]);
    }
}
