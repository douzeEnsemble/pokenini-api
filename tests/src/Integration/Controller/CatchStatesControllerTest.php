<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\CatchStatesController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(CatchStatesController::class)]
final class CatchStatesControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsArrayOfCatchStates(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
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
    public function getEachCatchStateHasRequiredFields(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var mixed $catchState */
        foreach ($data as $catchState) {
            self::assertIsArray($catchState);
            self::assertArrayHasKey('slug', $catchState);
            self::assertArrayHasKey('name', $catchState);
            self::assertArrayHasKey('french_name', $catchState);
            self::assertArrayHasKey('color', $catchState);
        }
    }

    #[Test]
    public function getFieldValuesAreStrings(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<array-key, mixed> $data */
        $data = json_decode($content, associative: true);

        /** @var mixed $firstCatchState */
        $firstCatchState = $data[0] ?? null;

        self::assertIsArray($firstCatchState);
        self::assertIsString($firstCatchState['slug']);
        self::assertIsString($firstCatchState['name']);
        self::assertIsString($firstCatchState['french_name']);
        self::assertIsString($firstCatchState['color']);
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/catch_states', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/catch_states_response.json',
            $content,
        );
    }
}
