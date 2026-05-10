<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CollectionsController;
use App\Service\CollectionsService;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CollectionsController::class)]
#[CoversClass(CollectionsService::class)]
final class CollectionsControllerTest extends AbstractTestControllerApi
{
    public function testGetCollection(): void
    {
        $this->apiRequest('GET', '/collections');

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(8, $content);

        $this->assertEquals([
            'name' => 'Sword, Shield - Dynamax Adventures bosses',
            'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            'slug' => 'swshdynamaxadventuresbosses',
        ], $content[0]);

        $this->assertEquals([
            'name' => "Scarlet, Violet - Terrarium's outbreaks",
            'french_name' => 'Scarlet, Violet - Apparitions massives du Terrarium',
            'slug' => 'svmassoutbreaksterrarium',
        ], $content[3]);

        $this->assertEquals([
            'name' => 'Pokemon Go - Dynamax',
            'french_name' => 'Pokemon Go - Dynamax',
            'slug' => 'pogodynamax',
        ], $content[7]);
    }

    public function testGetAuth(): void
    {
        $this->apiRequest('GET', '/types', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var string[] $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(18, $content);
    }

    public function testGetBadAuth(): void
    {
        $this->apiRequest('GET', '/types', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
