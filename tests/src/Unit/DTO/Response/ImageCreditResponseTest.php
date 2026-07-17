<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ImageCreditResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditResponse::class)]
final class ImageCreditResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesNameAndUrl(): void
    {
        $response = new ImageCreditResponse(name: 'PokéSprite', url: 'https://github.com/msikma/pokesprite');

        self::assertSame('PokéSprite', $response->name);
        self::assertSame('https://github.com/msikma/pokesprite', $response->url);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ImageCreditResponse(name: 'PokemonDB', url: 'https://pokemondb.net/sprites/bulbasaur');

        self::assertSame('PokemonDB', $response->name);
        self::assertSame('https://pokemondb.net/sprites/bulbasaur', $response->url);
    }
}
