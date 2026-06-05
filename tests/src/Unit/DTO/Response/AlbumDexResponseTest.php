<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumDexResponse;
use App\DTO\Response\AlbumRegionResponse;
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

        $response = new AlbumDexResponse(
            slug: 'redgreenblueyellow',
            originalSlug: 'redgreenblueyellow',
            name: 'Red / Green / Blue / Yellow',
            frenchName: 'Rouge / Vert / Bleu / Jaune',
            isShiny: false,
            isPrivate: false,
            isOnHome: false,
            isDisplayForm: true,
            displayTemplate: 'box',
            region: $region,
            selectionRule: '(p.bankable or p.bankableish) and ba?.redgreenblueyellow',
            description: 'The list of obtainable Pokémons in Red, Blue, Yellow and even Green games',
            frenchDescription: 'La liste des pokémons obtenable dans les jeux Rouge, Bleu, Jaune et même Vert.',
            version: '20230221.085100',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertSame('redgreenblueyellow', $response->slug);
        self::assertSame('redgreenblueyellow', $response->originalSlug);
        self::assertSame('Red / Green / Blue / Yellow', $response->name);
        self::assertSame('Rouge / Vert / Bleu / Jaune', $response->frenchName);
        self::assertFalse($response->isShiny);
        self::assertFalse($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
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
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function constructorAcceptsNullRegion(): void
    {
        $response = new AlbumDexResponse(
            slug: 'home',
            originalSlug: 'home',
            name: 'Home',
            frenchName: 'Home',
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            displayTemplate: 'box',
            region: null,
            selectionRule: '',
            description: '',
            frenchDescription: '',
            version: '20230421.123456',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertNull($response->region);
        self::assertTrue($response->isPrivate);
    }
}
