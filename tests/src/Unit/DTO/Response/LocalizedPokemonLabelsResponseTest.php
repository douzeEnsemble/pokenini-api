<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedPokemonLabelsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedPokemonLabelsResponse::class)]
final class LocalizedPokemonLabelsResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedPokemonLabelsResponse(
            names: ['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'],
            simplifiedNames: ['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'],
            formsLabel: 'Alolan',
        );

        self::assertSame(['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'], $response->names);
        self::assertSame(['en' => 'Bulbasaur', 'fr' => 'Bulbizarre'], $response->simplifiedNames);
        self::assertSame('Alolan', $response->formsLabel);
    }
}
