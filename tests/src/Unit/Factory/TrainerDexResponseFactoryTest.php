<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\TrainerDexResponse;
use App\Factory\TrainerDexResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerDexResponseFactory::class)]
final class TrainerDexResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowTransformsSingleRowCorrectly(): void
    {
        $row = [
            'dex_slug' => 'home',
            'name' => 'Home',
            'french_name' => 'Home',
            'slug' => 'home',
            'is_shiny' => false,
            'is_private' => true,
            'is_on_home' => false,
            'is_display_form' => true,
            'display_template' => 'box',
            'is_released' => true,
            'is_premium' => false,
            'is_custom' => false,
        ];

        $response = TrainerDexResponseFactory::fromSqlRow($row);

        self::assertSame('home', $response->dex->slug);
        self::assertSame('Home', $response->settings->name);
        self::assertSame('Home', $response->settings->frenchName);
        self::assertSame('home', $response->settings->slug);
        self::assertSame('box', $response->settings->displayTemplate);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowCastsValuesToCorrectTypes(): void
    {
        $row = [
            'dex_slug' => 123,
            'name' => 456,
            'french_name' => 789,
            'slug' => 101,
            'is_shiny' => 0,
            'is_private' => 1,
            'is_on_home' => 0,
            'is_display_form' => 1,
            'display_template' => 202,
            'is_released' => 1,
            'is_premium' => 0,
            'is_custom' => 0,
        ];

        $response = TrainerDexResponseFactory::fromSqlRow($row);

        self::assertSame('123', $response->dex->slug);
        self::assertSame('456', $response->settings->name);
        self::assertSame('789', $response->settings->frenchName);
        self::assertSame('101', $response->settings->slug);
        self::assertSame('202', $response->settings->displayTemplate);
        self::assertFalse($response->flags->isShiny);
        self::assertTrue($response->flags->isPrivate);
        self::assertFalse($response->flags->isOnHome);
        self::assertTrue($response->flags->isDisplayForm);
        self::assertTrue($response->flags->isReleased);
        self::assertFalse($response->flags->isPremium);
        self::assertFalse($response->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowsTransformsMultipleRowsCorrectly(): void
    {
        $rows = [
            [
                'dex_slug' => 'home',
                'name' => 'Home',
                'french_name' => 'Home',
                'slug' => 'home',
                'is_shiny' => false,
                'is_private' => true,
                'is_on_home' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_released' => true,
                'is_premium' => false,
                'is_custom' => false,
            ],
            [
                'dex_slug' => 'homeshiny',
                'name' => "Home\nShiny",
                'french_name' => "Home\nChromatique",
                'slug' => 'home_shiny',
                'is_shiny' => true,
                'is_private' => true,
                'is_on_home' => false,
                'is_display_form' => true,
                'display_template' => 'box',
                'is_released' => true,
                'is_premium' => true,
                'is_custom' => true,
            ],
        ];

        $responses = TrainerDexResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(TrainerDexResponse::class, $responses);
        self::assertSame('home', $responses[0]->dex->slug);
        self::assertFalse($responses[0]->flags->isShiny);
        self::assertSame('homeshiny', $responses[1]->dex->slug);
        self::assertTrue($responses[1]->flags->isShiny);
        self::assertTrue($responses[1]->flags->isCustom);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $responses = TrainerDexResponseFactory::fromSqlRows([]);

        self::assertCount(0, $responses);
    }
}
