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

    #[Test]
    public function getInTheRequestedLanguage(): void
    {
        $data = $this->getForms('fr');

        self::assertSame(['slug' => 'starter', 'name' => 'de Départ'], $data['category'][0]);
        self::assertSame(['slug' => 'alolan', 'name' => "d'Alola"], $data['regional'][0]);
        self::assertSame(['slug' => 'gigantamax', 'name' => 'Gigamax'], $data['special'][1]);
        self::assertSame(['slug' => 'gender', 'name' => 'Sexe'], $data['variant'][0]);
    }

    #[Test]
    public function getInTheFallbackLanguage(): void
    {
        $data = $this->getForms('en');

        self::assertSame(['slug' => 'starter', 'name' => 'Starter'], $data['category'][0]);
        self::assertSame(['slug' => 'alolan', 'name' => 'Alolan'], $data['regional'][0]);
        self::assertSame(['slug' => 'gigantamax', 'name' => 'Gigantamax'], $data['special'][1]);
        self::assertSame(['slug' => 'gender', 'name' => 'Gender'], $data['variant'][0]);
    }

    #[Test]
    public function getInANotEnabledLanguageIsABadRequest(): void
    {
        $client = self::createClient();
        $client->request('GET', '/forms?lang=de', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    /**
     * @return array<string, list<array<string, string>>>
     */
    private function getForms(string $lang): array
    {
        $client = self::createClient();
        $client->request('GET', '/forms?lang='.$lang, [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var array<string, list<array<string, string>>> */
        return json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
