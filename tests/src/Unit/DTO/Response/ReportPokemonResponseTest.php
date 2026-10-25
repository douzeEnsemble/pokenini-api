<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportPokemonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportPokemonResponse::class)]
final class ReportPokemonResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportPokemonResponse(
            slug: 'venusaur-f',
            name: 'Venusaur',
            frenchName: 'Florizarre',
            simplifiedName: 'Venusaur',
            simplifiedFrenchName: 'Florizarre',
            formsLabel: 'Female',
            formsFrenchLabel: 'Femelle',
            icon: 'venusaur-f',
        );

        self::assertSame('venusaur-f', $response->slug);
        self::assertSame('Venusaur', $response->name);
        self::assertSame('Florizarre', $response->frenchName);
        self::assertSame('Venusaur', $response->simplifiedName);
        self::assertSame('Florizarre', $response->simplifiedFrenchName);
        self::assertSame('Female', $response->formsLabel);
        self::assertSame('Femelle', $response->formsFrenchLabel);
        self::assertSame('venusaur-f', $response->icon);
    }
}
