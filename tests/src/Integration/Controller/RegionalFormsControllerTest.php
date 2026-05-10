<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\RegionalFormsController;
use App\Service\RegionalFormsService;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RegionalFormsController::class)]
#[CoversClass(RegionalFormsService::class)]
final class RegionalFormsControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', '/forms/regional');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(3, $content);

        $this->assertEquals([
            'name' => 'Alolan',
            'french_name' => "d'Alola",
            'slug' => 'alolan',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Hisuian',
            'french_name' => 'de Hisui',
            'slug' => 'hisuian',
        ], $content[2]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/forms/regional', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(3, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/forms/regional', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
