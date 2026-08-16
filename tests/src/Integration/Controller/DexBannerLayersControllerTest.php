<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexBannerLayersController;
use App\Entity\Dex;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(DexBannerLayersController::class)]
final class DexBannerLayersControllerTest extends AbstractTestControllerApi
{
    #[Test]
    public function getWithIconCredentials(): void
    {
        $this->apiRequest(
            'GET',
            '/istration/dex-banner-layers',
            [],
            ['PHP_AUTH_USER' => self::ICON_AUTH_USER, 'PHP_AUTH_PW' => self::ICON_AUTH_PASSWORD]
        );

        $this->assertJsonResponseIsOK();

        $content = $this->getJsonDecodedResponseContent();

        $this->assertArrayHasKey('goldsilvercrystal', $content);
        $this->assertSame(['shiny', 'mega'], $content['goldsilvercrystal']);
        $this->assertArrayNotHasKey('redgreenblueyellow', $content);
        $this->assertArrayNotHasKey('home', $content);
    }

    #[Test]
    public function getWithWebCredentialsAlsoWorks(): void
    {
        $this->apiRequest('GET', '/istration/dex-banner-layers');

        $this->assertJsonResponseIsOK();
    }

    #[Test]
    public function getWithoutCredentialsIsRejected(): void
    {
        $this->apiRequest('GET', '/istration/dex-banner-layers', [], []);

        $this->assertEquals(401, $this->getClientResponse()->getStatusCode());
    }

    #[Test]
    public function iconCredentialsAreRejectedOnOtherRoutes(): void
    {
        $this->apiRequest('GET', '/types', [], ['PHP_AUTH_USER' => self::ICON_AUTH_USER, 'PHP_AUTH_PW' => self::ICON_AUTH_PASSWORD]);

        $this->assertEquals(403, $this->getClientResponse()->getStatusCode());
    }

    #[Test]
    public function getReturnsAnEmptyJsonObjectWhenNoDexHasBannerLayers(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $dex = $entityManager->getRepository(Dex::class)->findOneBy(['slug' => 'goldsilvercrystal']);
        $this->assertNotNull($dex);
        $dex->bannerLayers = null;
        $entityManager->flush();

        $this->apiRequest(
            'GET',
            '/istration/dex-banner-layers',
            [],
            ['PHP_AUTH_USER' => self::ICON_AUTH_USER, 'PHP_AUTH_PW' => self::ICON_AUTH_PASSWORD]
        );

        $this->assertJsonResponseIsOK();
        $this->assertSame('{}', $this->getClientResponseContent());
    }
}
