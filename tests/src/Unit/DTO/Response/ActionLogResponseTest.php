<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ActionLogEntryResponse;
use App\DTO\Response\ActionLogResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogResponse::class)]
final class ActionLogResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $current = new ActionLogEntryResponse(
            createdAt: '2026-05-25 10:00:00+00',
            doneAt: '2026-05-25 10:01:00+00',
            executionTime: '60',
            details: null,
            errorTrace: null,
        );
        $last = new ActionLogEntryResponse(
            createdAt: '2026-05-24 09:00:00+00',
            doneAt: null,
            executionTime: null,
            details: null,
            errorTrace: null,
        );

        $response = new ActionLogResponse(current: $current, last: $last);

        self::assertSame($current, $response->current);
        self::assertSame($last, $response->last);
    }

    #[Test]
    public function constructorAcceptsNullEntries(): void
    {
        $response = new ActionLogResponse(current: null, last: null);

        self::assertNull($response->current);
        self::assertNull($response->last);
    }
}
