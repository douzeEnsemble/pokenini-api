<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ActionLogEntryResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogEntryResponse::class)]
final class ActionLogEntryResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ActionLogEntryResponse(
            createdAt: '2026-05-25 10:00:00+00',
            doneAt: '2026-05-25 10:01:00+00',
            executionTime: '60',
            details: ['nb_pokemons' => '1008'],
            errorTrace: null,
        );

        self::assertSame('2026-05-25 10:00:00+00', $response->createdAt);
        self::assertSame('2026-05-25 10:01:00+00', $response->doneAt);
        self::assertSame('60', $response->executionTime);
        self::assertSame(['nb_pokemons' => '1008'], $response->details);
        self::assertNull($response->errorTrace);
    }

    #[Test]
    public function constructorAcceptsNullableProperties(): void
    {
        $response = new ActionLogEntryResponse(
            createdAt: '2026-05-25 09:00:00+00',
            doneAt: null,
            executionTime: null,
            details: null,
            errorTrace: 'Exception: something went wrong',
        );

        self::assertSame('2026-05-25 09:00:00+00', $response->createdAt);
        self::assertNull($response->doneAt);
        self::assertNull($response->executionTime);
        self::assertNull($response->details);
        self::assertSame('Exception: something went wrong', $response->errorTrace);
    }
}
