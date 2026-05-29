<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormResponse;
use App\DTO\Response\FormsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormsResponse::class)]
final class FormsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $category = new FormResponse('mega', 'Mega');
        $regional = new FormResponse('alolan', 'Alolan');
        $special = new FormResponse('gmax', 'Gigantamax');
        $variant = new FormResponse('original', 'Original Cap');

        $response = new FormsResponse(
            category: $category,
            regional: $regional,
            special: $special,
            variant: $variant,
        );

        self::assertSame($category, $response->category);
        self::assertSame($regional, $response->regional);
        self::assertSame($special, $response->special);
        self::assertSame($variant, $response->variant);
    }

    #[Test]
    public function constructorAcceptsNullProperties(): void
    {
        $response = new FormsResponse(
            category: null,
            regional: null,
            special: null,
            variant: null,
        );

        self::assertNull($response->category);
        self::assertNull($response->regional);
        self::assertNull($response->special);
        self::assertNull($response->variant);
    }
}
