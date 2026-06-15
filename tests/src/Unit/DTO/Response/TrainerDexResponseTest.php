<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexFlagsResponse;
use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\TrainerDexResponse;
use App\DTO\Response\TrainerDexSettingsResponse;
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
        $settings = new TrainerDexSettingsResponse(
            name: 'Home',
            frenchName: 'Home',
            slug: 'home',
            displayTemplate: 'box',
        );
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: true,
            isOnHome: false,
            isDisplayForm: true,
            isReleased: true,
            isPremium: false,
            isCustom: false,
        );

        $response = new TrainerDexResponse(
            dex: $dex,
            settings: $settings,
            flags: $flags,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('home', $response->dex->slug);
        self::assertSame($settings, $response->settings);
        self::assertSame('Home', $response->settings->name);
        self::assertSame('Home', $response->settings->frenchName);
        self::assertSame('home', $response->settings->slug);
        self::assertSame('box', $response->settings->displayTemplate);
        self::assertSame($flags, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $dex = new DexSlugResponse(slug: 'homepogo');
        $settings = new TrainerDexSettingsResponse(
            name: 'Home PoGo',
            frenchName: 'Home PoGo',
            slug: 'home_pogo',
            displayTemplate: 'list-7',
        );
        $flags = new DexFlagsResponse(
            isShiny: false,
            isPrivate: false,
            isOnHome: true,
            isDisplayForm: false,
            isReleased: false,
            isPremium: true,
            isCustom: true,
        );

        $response = new TrainerDexResponse(
            dex: $dex,
            settings: $settings,
            flags: $flags,
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('homepogo', $response->dex->slug);
        self::assertSame($settings, $response->settings);
        self::assertSame('Home PoGo', $response->settings->name);
        self::assertSame('Home PoGo', $response->settings->frenchName);
        self::assertSame('home_pogo', $response->settings->slug);
        self::assertSame('list-7', $response->settings->displayTemplate);
        self::assertSame($flags, $response->flags);
        self::assertFalse($response->flags->isShiny);
        self::assertFalse($response->flags->isPrivate);
        self::assertTrue($response->flags->isOnHome);
        self::assertFalse($response->flags->isDisplayForm);
        self::assertFalse($response->flags->isReleased);
        self::assertTrue($response->flags->isPremium);
        self::assertTrue($response->flags->isCustom);
    }
}
