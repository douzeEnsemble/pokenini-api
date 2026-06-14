<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\PokemonDebugBankResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonDebugBankResponse::class)]
final class PokemonDebugBankResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new PokemonDebugBankResponse(bankable: true, bankableish: false);

        self::assertTrue($response->bankable);
        self::assertFalse($response->bankableish);
    }

    #[Test]
    public function constructorAcceptsBankableishAsNull(): void
    {
        $response = new PokemonDebugBankResponse(bankable: false, bankableish: null);

        self::assertFalse($response->bankable);
        self::assertNull($response->bankableish);
    }
}
