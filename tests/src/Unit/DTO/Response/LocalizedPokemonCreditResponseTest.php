<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ImageCreditResponse;
use App\DTO\Response\LocalizedPokemonCreditResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedPokemonCreditResponse::class)]
final class LocalizedPokemonCreditResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $smallRegular = new ImageCreditResponse('a');
        $bigShiny = new ImageCreditResponse('d');

        $response = new LocalizedPokemonCreditResponse(
            pokemonSlug: 'bulbasaur',
            pokemonName: 'Bulbizarre',
            pokemonIcon: 'bulbasaur-icon',
            smallRegularCredit: $smallRegular,
            smallShinyCredit: null,
            bigRegularCredit: null,
            bigShinyCredit: $bigShiny,
        );

        self::assertSame('bulbasaur', $response->pokemonSlug);
        self::assertSame('Bulbizarre', $response->pokemonName);
        self::assertSame('bulbasaur-icon', $response->pokemonIcon);
        self::assertSame($smallRegular, $response->smallRegularCredit);
        self::assertNull($response->smallShinyCredit);
        self::assertNull($response->bigRegularCredit);
        self::assertSame($bigShiny, $response->bigShinyCredit);
    }
}
