<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TypesController;
use App\Service\TypesService;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TypesController::class)]
#[CoversClass(TypesService::class)]
class TypesControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', '/types');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(18, $content);

        $this->assertEquals([
            'name' => 'Normal',
            'frenchName' => 'Normal',
            'slug' => 'normal',
            'color' => '#A8A878',
        ], $content[0]);

        $this->assertEquals([
            'name' => 'Poison',
            'frenchName' => 'Poison',
            'slug' => 'poison',
            'color' => '#A040A0',
        ], $content[3]);

        $this->assertEquals([
            'name' => 'Ghost',
            'frenchName' => 'Spectre',
            'slug' => 'ghost',
            'color' => '#705898',
        ], $content[7]);

        $this->assertEquals([
            'name' => 'Stellar',
            'frenchName' => 'Stellaire',
            'slug' => 'stellar',
            'color' => '#7CC7B2',
        ], $content[17]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/types', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'douze']);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(18, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/types', [], ['PHP_AUTH_USER' => 'web', 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
