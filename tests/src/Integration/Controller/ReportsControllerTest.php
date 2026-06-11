<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ReportsController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ReportsController::class)]
final class ReportsControllerTest extends WebTestCase
{
    #[Test]
    public function getReturnsSuccessfulJsonResponse(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    #[Test]
    public function getReturnsObjectWithRequiredSections(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);
        self::assertArrayHasKey('catch_state_counts_defined_by_trainer', $data);
        self::assertArrayHasKey('dex_usage', $data);
        self::assertArrayHasKey('catch_state_usage', $data);
    }

    #[Test]
    public function getCatchStateCountsHaveCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var array<int, mixed> $catchStateCounts */
        $catchStateCounts = $data['catch_state_counts_defined_by_trainer'];

        /** @var mixed $item */
        foreach ($catchStateCounts as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('count', $item);
            self::assertIsInt($item['count']);
            self::assertArrayHasKey('trainer', $item);
            self::assertIsArray($item['trainer']);
            self::assertArrayHasKey('external_id', $item['trainer']);
            self::assertIsString($item['trainer']['external_id']);
        }
    }

    #[Test]
    public function getDexUsageHasCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var array<int, mixed> $dexUsage */
        $dexUsage = $data['dex_usage'];

        /** @var mixed $item */
        foreach ($dexUsage as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('count', $item);
            self::assertIsInt($item['count']);
            self::assertArrayHasKey('dex', $item);
            self::assertIsArray($item['dex']);
            self::assertArrayHasKey('name', $item['dex']);
            self::assertArrayHasKey('french_name', $item['dex']);
            self::assertIsString($item['dex']['name']);
            self::assertIsString($item['dex']['french_name']);
        }
    }

    #[Test]
    public function getCatchStateUsageHasCorrectShape(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var null|array<string, mixed> $data */
        $data = json_decode($content, associative: true);

        self::assertIsArray($data);

        /** @var array<int, mixed> $catchStateUsage */
        $catchStateUsage = $data['catch_state_usage'];

        /** @var mixed $item */
        foreach ($catchStateUsage as $item) {
            self::assertIsArray($item);
            self::assertArrayHasKey('count', $item);
            self::assertIsInt($item['count']);
            self::assertArrayHasKey('catch_state', $item);
            self::assertIsArray($item['catch_state']);
            self::assertArrayHasKey('name', $item['catch_state']);
            self::assertArrayHasKey('french_name', $item['catch_state']);
            self::assertArrayHasKey('color', $item['catch_state']);
            self::assertIsString($item['catch_state']['name']);
            self::assertIsString($item['catch_state']['french_name']);
            self::assertIsString($item['catch_state']['color']);
        }
    }

    #[Test]
    public function getResponseMatchesFixture(): void
    {
        $client = self::createClient();
        $client->request('GET', '/reports', [], [], [
            'PHP_AUTH_USER' => 'web',
            'PHP_AUTH_PW' => 'douze',
        ]);

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        self::assertJsonStringEqualsJsonFile(
            '/app/tests/resources/fixtures/reports_response.json',
            $content,
        );
    }
}
