<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\TypesController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(TypesController::class)]
final class TypesControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/types', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfTypes(): void
    {
        $client = self::createClient();
        $client->request('GET', '/types', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);
        self::assertNotEmpty($data);
    }

    #[Test]
    public function getEachTypeHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/types', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $type */
        foreach ($data as $type) {
            self::assertIsArray($type);
            self::assertArrayHasKey('slug', $type);
            self::assertArrayHasKey('name', $type);
            self::assertArrayHasKey('french_name', $type);
            self::assertArrayHasKey('color', $type);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/types', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstType */
        $firstType = $data[0] ?? null;

        self::assertIsArray($firstType);
        self::assertIsString($firstType['slug']);
        self::assertIsString($firstType['name']);
        self::assertIsString($firstType['french_name']);
        self::assertIsString($firstType['color']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/types', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/types_response.json',
            $content,
        );
    }
}
