<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CategoryFormsController;
use App\Service\CategoryFormsService;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CategoryFormsController::class)]
#[CoversClass(CategoryFormsService::class)]
final class CategoryFormsControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', '/forms/category');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(3, $content);

        $this->assertEquals([
            'name' => 'Starter',
            'french_name' => 'de Départ',
            'slug' => 'starter',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Legendary',
            'french_name' => 'Légendaire',
            'slug' => 'legendary',
        ], $content[2]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/forms/category', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(3, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/forms/category', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
