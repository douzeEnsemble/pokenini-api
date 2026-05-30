<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexResponse::class)]
final class DexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new DexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            isShiny: false,
            isDisplayForm: true,
            description: 'The National Dex in Home',
            frenchDescription: 'Le Pokédex National dans Home',
            isReleased: true,
            isPremium: false,
            dexTotalCount: 22,
        );

        self::assertSame('home', $response->slug);
        self::assertSame('home', $response->originalSlug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('The National Dex in Home', $response->description);
        self::assertSame('Le Pokédex National dans Home', $response->frenchDescription);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertSame(22, $response->dexTotalCount);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new DexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            isShiny: true,
            isDisplayForm: false,
            description: '',
            frenchDescription: '',
            isReleased: true,
            isPremium: true,
            dexTotalCount: 7,
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertTrue($response->isShiny);
        self::assertFalse($response->isDisplayForm);
        self::assertSame('', $response->description);
        self::assertSame('', $response->frenchDescription);
        self::assertTrue($response->isReleased);
        self::assertTrue($response->isPremium);
        self::assertSame(7, $response->dexTotalCount);
    }
}
