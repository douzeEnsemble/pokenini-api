<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonSlugResponse::class)]
final class PokemonSlugResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesSlug(): void
    {
        $response = new PokemonSlugResponse(slug: 'pikachu');

        self::assertSame('pikachu', $response->slug);
    }

    #[Test]
    public function slugIsReadonly(): void
    {
        $response = new PokemonSlugResponse(slug: 'charizard');

        self::assertSame('charizard', $response->slug);
    }
}
