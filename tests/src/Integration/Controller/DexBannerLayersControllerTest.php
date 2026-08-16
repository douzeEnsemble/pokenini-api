<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\DexBannerLayersController;
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

        $this->assertArrayHasKey('redgreenblueyellow', $content);
        $this->assertSame(['shiny', 'mega'], $content['redgreenblueyellow']);
        $this->assertArrayNotHasKey('goldsilvercrystal', $content);
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
}
