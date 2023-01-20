<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

class CatchStatesControllerTest extends AbstractControllerApiTest
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', 'catch_states');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertEquals([
            'name' => 'No',
            'frenchName' => 'Non',
            'slug' => 'no',
            'color' => '#e57373',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Maybe',
            'frenchName' => 'Peut être',
            'slug' => 'maybe',
            'color' => 'blue',
        ], $content[1]);

        $this->assertEquals([
            'name' => 'Maybe not',
            'frenchName' => 'Peut être pas',
            'slug' => 'maybenot',
            'color' => 'yellow',
        ], $content[2]);

        $this->assertEquals([
            'name' => 'Yes',
            'frenchName' => 'Oui',
            'slug' => 'yes',
            'color' => '#66bb6a',
        ], $content[3]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', 'catch_states', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', 'catch_states', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getResponse()->getStatusCode());
    }
}
