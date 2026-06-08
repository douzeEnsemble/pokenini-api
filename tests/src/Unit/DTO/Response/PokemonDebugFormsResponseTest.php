<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\FormDebugResponse;
use App\DTO\Response\PokemonDebugFormsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugFormsResponse::class)]
final class PokemonDebugFormsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $category = new FormDebugResponse(null, 'starter', 'Starter', 'Starter', 1, null);
        $regional = new FormDebugResponse(null, 'alolan', 'Alolan', "d'Alola", 2, null);
        $special = new FormDebugResponse(null, 'mega', 'Mega', 'Méga', 3, null);
        $variant = new FormDebugResponse(null, 'gender', 'Gender', 'Genre', 4, null);

        $response = new PokemonDebugFormsResponse(
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
        $response = new PokemonDebugFormsResponse(
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
