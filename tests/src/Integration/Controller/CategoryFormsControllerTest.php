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
class CategoryFormsControllerTest extends AbstractTestControllerApi
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
            'frenchName' => 'de Départ',
            'slug' => 'starter',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Legendary',
            'frenchName' => 'Légendaire',
            'slug' => 'legendary',
        ], $content[2]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/forms/category', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(3, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/forms/category', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getResponse()->getStatusCode());
    }
}
