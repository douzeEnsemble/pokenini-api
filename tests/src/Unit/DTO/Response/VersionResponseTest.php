<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\VersionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VersionResponse::class)]
final class VersionResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $updatedAt = new \DateTimeImmutable('2026-11-08T10:00:00+00:00');

        $response = new VersionResponse('1.2.12', $updatedAt);

        self::assertSame('1.2.12', $response->version);
        self::assertSame($updatedAt, $response->updatedAt);
    }

    #[Test]
    public function updatedAtCanBeUnknown(): void
    {
        $response = new VersionResponse('unknown', null);

        self::assertSame('unknown', $response->version);
        self::assertNull($response->updatedAt);
    }
}
