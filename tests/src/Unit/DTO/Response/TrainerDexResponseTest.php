<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexResponse::class)]
final class TrainerDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $dex = new DexSlugResponse(slug: 'home');

        $response = new TrainerDexResponse(
            dex: $dex,
            name: 'Home',
            frenchName: 'Home',
            slug: 'home',
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            displayTemplate: 'box',
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertTrue($response->isPrivate);
        self::assertFalse($response->isOnHome);
        self::assertTrue($response->isDisplayForm);
        self::assertSame('box', $response->displayTemplate);
        self::assertTrue($response->isReleased);
        self::assertFalse($response->isPremium);
        self::assertFalse($response->isCustom);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $dex = new DexSlugResponse(slug: 'homepogo');

        $response = new TrainerDexResponse(
            dex: $dex,
            name: 'Home PoGo',
            frenchName: 'Home PoGo',
            slug: 'home_pogo',
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: false,
            displayTemplate: 'list-7',
            isReleased: false,
            isPremium: true,
            isCustom: true,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('homepogo', $response->dex->slug);
        self::assertSame('Home PoGo', $response->name);
        self::assertSame('Home PoGo', $response->frenchName);
        self::assertSame('home_pogo', $response->slug);
        self::assertFalse($response->isShiny);
        self::assertFalse($response->isPrivate);
        self::assertTrue($response->isOnHome);
        self::assertFalse($response->isDisplayForm);
        self::assertSame('list-7', $response->displayTemplate);
        self::assertFalse($response->isReleased);
        self::assertTrue($response->isPremium);
        self::assertTrue($response->isCustom);
    }
}
