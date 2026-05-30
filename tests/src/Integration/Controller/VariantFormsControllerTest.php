<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\VariantFormsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(VariantFormsController::class)]
final class VariantFormsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfForms(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
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
    public function getEachFormHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $form */
        foreach ($data as $form) {
            self::assertIsArray($form);
            self::assertArrayHasKey('slug', $form);
            self::assertArrayHasKey('name', $form);
            self::assertArrayHasKey('french_name', $form);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstForm */
        $firstForm = $data[0] ?? null;

        self::assertIsArray($firstForm);
        self::assertIsString($firstForm['slug']);
        self::assertIsString($firstForm['name']);
        self::assertIsString($firstForm['french_name']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms/variant', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/forms_variant_response.json',
            $content,
        );
    }
}
