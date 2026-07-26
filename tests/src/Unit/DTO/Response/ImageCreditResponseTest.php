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
    public function constructorInitializesCredit(): void
    {
        $response = new ImageCreditResponse(credit: 'PokéSprite - https://github.com/msikma/pokesprite');

        self::assertSame('PokéSprite - https://github.com/msikma/pokesprite', $response->credit);
    }

    #[Test]
    public function propertyIsReadonly(): void
    {
        $response = new ImageCreditResponse(credit: 'PokemonDB - https://pokemondb.net/sprites/bulbasaur');

        self::assertSame('PokemonDB - https://pokemondb.net/sprites/bulbasaur', $response->credit);
    }
}
