<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\AlbumFormResponse;
use App\DTO\Response\AlbumFormsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AlbumFormsResponse::class)]
final class AlbumFormsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $category = new AlbumFormResponse('starter', 'Starter', 'de Départ');
        $regional = new AlbumFormResponse('alolan', 'Alolan', "d'Alola");
        $special = new AlbumFormResponse('mega', 'Mega', 'Mega');
        $variant = new AlbumFormResponse('gender', 'Gender', 'Sexe');

        $response = new AlbumFormsResponse(
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
        $response = new AlbumFormsResponse(
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
