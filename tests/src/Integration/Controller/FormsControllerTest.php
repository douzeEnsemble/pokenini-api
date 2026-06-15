<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\FormsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(FormsController::class)]
final class FormsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getResponseHasRequiredKeys(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);
        self::assertArrayHasKey('category', $data);
        self::assertArrayHasKey('regional', $data);
        self::assertArrayHasKey('special', $data);
        self::assertArrayHasKey('variant', $data);
    }

    #[Test]
    public function getEachTypeIsArrayOfForms(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        foreach (['category', 'regional', 'special', 'variant'] as $key) {
            self::assertIsArray($data[$key]);
            self::assertNotEmpty($data[$key]);
        }
    }

    #[Test]
    public function getEachFormHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        foreach (['category', 'regional', 'special', 'variant'] as $key) {
            /** @var mixed[] $forms */
            $forms = $data[$key];

            /** @var mixed $form */
            foreach ($forms as $form) {
                self::assertIsArray($form);
                self::assertArrayHasKey('slug', $form);
                self::assertArrayHasKey('name', $form);
                self::assertArrayHasKey('french_name', $form);
            }
        }
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/forms_response.json',
            $content,
        );
    }
}
