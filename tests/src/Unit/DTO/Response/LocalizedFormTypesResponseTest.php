<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedFormResponse;
use App\DTO\Response\LocalizedFormTypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedFormTypesResponse::class)]
final class LocalizedFormTypesResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $category = [new LocalizedFormResponse('starter', 'de Départ')];
        $regional = [new LocalizedFormResponse('alolan', "d'Alola")];
        $special = [new LocalizedFormResponse('mega', 'Mega')];
        $variant = [new LocalizedFormResponse('gender', 'Sexe')];

        $response = new LocalizedFormTypesResponse($category, $regional, $special, $variant);

        self::assertSame($category, $response->category);
        self::assertSame($regional, $response->regional);
        self::assertSame($special, $response->special);
        self::assertSame($variant, $response->variant);
    }
}
