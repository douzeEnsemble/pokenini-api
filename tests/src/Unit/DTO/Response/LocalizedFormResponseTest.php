<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\LocalizedFormResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalizedFormResponse::class)]
final class LocalizedFormResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new LocalizedFormResponse(slug: 'alolan', name: "d'Alola");

        self::assertSame('alolan', $response->slug);
        self::assertSame("d'Alola", $response->name);
    }
}
