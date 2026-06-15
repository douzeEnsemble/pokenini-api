<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerDexSettingsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexSettingsResponse::class)]
final class TrainerDexSettingsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TrainerDexSettingsResponse(
            name: 'Home',
            frenchName: 'Home',
            slug: 'home',
            displayTemplate: 'box',
        );

        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
        self::assertSame('home', $response->slug);
        self::assertSame('box', $response->displayTemplate);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new TrainerDexSettingsResponse(
            name: 'Home PoGo',
            frenchName: 'Home PoGo',
            slug: 'home_pogo',
            displayTemplate: 'list-7',
        );

        self::assertSame('Home PoGo', $response->name);
        self::assertSame('Home PoGo', $response->frenchName);
        self::assertSame('home_pogo', $response->slug);
        self::assertSame('list-7', $response->displayTemplate);
    }
}
