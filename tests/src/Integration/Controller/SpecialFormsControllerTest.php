<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\SpecialFormsController;
use App\Service\SpecialFormsService;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SpecialFormsController::class)]
#[CoversClass(SpecialFormsService::class)]
final class SpecialFormsControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', '/forms/special');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);

        $this->assertEquals([
            'name' => 'Mega',
            'french_name' => 'Mega',
            'slug' => 'mega',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Alpha',
            'french_name' => 'Baron',
            'slug' => 'alpha',
        ], $content[2]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/forms/special', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(4, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/forms/special', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
