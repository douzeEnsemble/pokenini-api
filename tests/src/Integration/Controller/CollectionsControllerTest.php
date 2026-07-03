<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CollectionsController;
use App\Factory\CollectionResponseFactory;
use App\Service\CollectionsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(CollectionsController::class)]
#[CoversClass(CollectionResponseFactory::class)]
#[CoversClass(CollectionsService::class)]
final class CollectionsControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function getReturnsCollections(): void
    {
        $this->apiRequest('GET', '/collections');

        $this->assertResponseIsOK();

        /** @var array<array-key, mixed> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(8, $content);

        $this->assertEquals([
            'slug' => 'swshdynamaxadventuresbosses',
            'name' => 'Sword, Shield - Dynamax Adventures bosses',
            'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            'order_number' => 11,
        ], $content[0]);

        $this->assertEquals([
            'slug' => 'svmassoutbreaksterrarium',
            'name' => "Scarlet, Violet - Terrarium's outbreaks",
            'french_name' => 'Scarlet, Violet - Apparitions massives du Terrarium',
            'order_number' => 22,
        ], $content[3]);

        $this->assertEquals([
            'slug' => 'pogodynamax',
            'name' => 'Pokemon Go - Dynamax',
            'french_name' => 'Pokemon Go - Dynamax',
            'order_number' => 52,
        ], $content[7]);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $this->apiRequest('GET', '/collections');

        $this->assertResponseIsOK();

        $response = $this->getClientResponse();
        $content = $response->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/collections_response.json',
            $content,
        );
    }

    #[Test]
    public function getReturnsOkWithAuth(): void
    {
        $this->apiRequest('GET', '/collections', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PASSWORD]);

        $this->assertResponseIsOK();

        /** @var array<array-key, mixed> $content */
        $content = $this->getJsonDecodedResponseContent();

        $this->assertCount(8, $content);
    }

    #[Test]
    public function getReturnsBadAuthWith401(): void
    {
        $this->apiRequest('GET', '/collections', [], ['PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => 'treize']);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }
}
