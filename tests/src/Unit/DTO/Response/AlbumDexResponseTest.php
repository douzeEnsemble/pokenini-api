<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
use App\DTO\Response\DexFlagsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumDexResponse::class)]
final class AlbumDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesAllProperties(): void
    {
        $region = new AlbumRegionResponse(name: 'Kanto', frenchName: 'Kanto');
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new AlbumDexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            flags: $flags,
            displayTemplate: 'box',
            region: $region,
            selectionRule: '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            description: 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            frenchDescription: 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            version: '20230221.085100',
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertSame($flags, $response->flags);
        self::assertSame('box', $response->displayTemplate);
        self::assertSame($region, $response->region);
        self::assertSame('(p.bankable or p.bankableish) and ba?.redgreenblueyellow', $response->selectionRule);
        self::assertSame(
            'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            $response->description,
        );
        self::assertSame(
            'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            $response->frenchDescription,
        );
        self::assertSame('20230221.085100', $response->version);
    }

    #[Test]
    public function constructorAcceptsNullRegion(): void
    {
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new AlbumDexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            flags: $flags,
            displayTemplate: 'box',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '20230421.123456',
        );

        self::assertNull($response->region);
        self::assertSame($flags, $response->flags);
        self::assertTrue($response->flags->isPrivate);
    }
}
