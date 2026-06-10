<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexDebugFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexDebugFlagsResponse::class)]
final class DexDebugFlagsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: true,
            isPremium: false,
            isDisplayForm: true,
            isReleased: false,
            canHoldElection: true,
        );

        self::assertTrue($flags->isShiny);
        self::assertFalse($flags->isPremium);
        self::assertTrue($flags->isDisplayForm);
        self::assertFalse($flags->isReleased);
        self::assertTrue($flags->canHoldElection);
    }

    #[Test]
    public function constructorAcceptsAllFalse(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: false,
            isPremium: false,
            isDisplayForm: false,
            isReleased: false,
            canHoldElection: false,
        );

        self::assertFalse($flags->isShiny);
        self::assertFalse($flags->isPremium);
        self::assertFalse($flags->isDisplayForm);
        self::assertFalse($flags->isReleased);
        self::assertFalse($flags->canHoldElection);
    }

    #[Test]
    public function constructorAcceptsAllTrue(): void
    {
        $flags = new DexDebugFlagsResponse(
            isShiny: true,
            isPremium: true,
            isDisplayForm: true,
            isReleased: true,
            canHoldElection: true,
        );

        self::assertTrue($flags->isShiny);
        self::assertTrue($flags->isPremium);
        self::assertTrue($flags->isDisplayForm);
        self::assertTrue($flags->isReleased);
        self::assertTrue($flags->canHoldElection);
    }
}
