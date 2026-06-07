<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexFlagsResponse::class)]
final class DexFlagsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new DexFlagsResponse(
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function constructorHandlesAllTrue(): void
    {
        $response = new DexFlagsResponse(
            isShiny: true,
            isPrivate: true,
            isOnHome: true,
            isDisplayForm: true,
            isReleased: true,
            isPremium: true,
            isCustom: true,
        );

        self::assertTrue($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertTrue($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertTrue($response->isReleased);
        self::assertTrue($response->isPremium);
        self::assertTrue($response->isCustom);
    }

    #[Test]
    public function constructorHandlesAllFalse(): void
    {
        $response = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: false,
            isReleased: false,
            isPremium: false,
            isCustom: false,
        );

        self::assertFalse($response->isShiny);
        self::assertFalse($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertFalse($response->isDisplayForm);
        self::assertFalse($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }
}
