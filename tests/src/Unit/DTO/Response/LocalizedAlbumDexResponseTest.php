<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\LocalizedAlbumDexResponse;
use App\DTO\Response\LocalizedAlbumRegionResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedAlbumDexResponse::class)]
final class LocalizedAlbumDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedAlbumDexResponse(
            slug: 'rgby',
            originalSlug: 'rgby-original',
            name: 'Rouge / Vert',
            flags: new DexFlagsResponse(
                isShiny: false,
                isPrivate: true,
                isOnHome: false,
                isDisplayForm: true,
                isReleased: true,
                isPremium: false,
                isCustom: false,
                isOnHub: false,
            ),
            displayTemplate: 'box',
            region: new LocalizedAlbumRegionResponse('Kanto'),
            selectionRule: 'rule',
            description: 'La liste',
            version: '1.0',
        );

        self::assertSame('rgby', $response->slug);
        self::assertSame('rgby-original', $response->originalSlug);
        self::assertSame('Rouge / Vert', $response->name);
        self::assertTrue($response->flags->isPrivate);
        self::assertSame('box', $response->displayTemplate);
        self::assertSame('Kanto', $response->region?->name);
        self::assertSame('rule', $response->selectionRule);
        self::assertSame('La liste', $response->description);
        self::assertSame('1.0', $response->version);
    }
}
