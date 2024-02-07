<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

class SpecialFormsControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', 'forms/special');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);

        $this->assertEquals([
            'name' => 'Mega',
            'frenchName' => 'Mega',
            'slug' => 'mega',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Alpha',
            'frenchName' => 'Baron',
            'slug' => 'alpha',
        ], $content[2]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', 'forms/special', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', 'forms/special', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getResponse()->getStatusCode());
    }
}
