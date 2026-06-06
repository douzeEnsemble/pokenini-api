<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexAvailabilitiesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesResponse::class)]
final class DexAvailabilitiesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new DexAvailabilitiesResponse(
            pokemons: ['bulbasaur', 'ivysaur'],
        );

        self::assertSame(['bulbasaur', 'ivysaur'], $response->pokemons);
    }

    #[Test]
    public function constructorAcceptsEmptyArray(): void
    {
        $response = new DexAvailabilitiesResponse(
            pokemons: [],
        );

        self::assertSame([], $response->pokemons);
    }
}
