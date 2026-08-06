<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\VersionController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(VersionController::class)]
final class VersionControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/version', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsVersionFromMetadataFile(): void
    {
        $client = self::createClient();
        $client->request('GET', '/version', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        $versionFilePath = dirname(__DIR__, 4).'/resources/metadata/version';
        $expectedVersion = trim((string) file_get_contents($versionFilePath));
        $expectedUpdatedAt = (new \DateTimeImmutable())->setTimestamp((int) filemtime($versionFilePath));

        self::assertJsonStringEqualsJsonString(
            json_encode([
                'version' => $expectedVersion,
                'updated_at' => $expectedUpdatedAt->format(\DateTimeInterface::ATOM),
            ], JSON_THROW_ON_ERROR),
            $content,
        );
    }

    #[Test]
    public function getNonAuthenticatedReturns401(): void
    {
        $client = self::createClient();
        $client->request('GET', '/version');

        self::assertResponseStatusCodeSame(401);
    }
}
