<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDebugFamilyResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugFamilyResponse::class)]
final class PokemonDebugFamilyResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new PokemonDebugFamilyResponse(slug: 'bulbasaur');

        self::assertSame('bulbasaur', $response->slug);
    }

    #[Test]
    public function constructorStoresSlugValue(): void
    {
        $response = new PokemonDebugFamilyResponse(slug: 'charmander');

        self::assertSame('charmander', $response->slug);
    }
}
