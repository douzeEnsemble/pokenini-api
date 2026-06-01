<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexUsageResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexUsageResponse::class)]
final class DexUsageResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new DexUsageResponse(
            count: 2,
            name: 'Home',
            frenchName: 'Home',
        );

        self::assertSame(2, $response->count);
        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new DexUsageResponse(
            count: 1,
            name: 'Ruby / Sapphire / Emerald',
            frenchName: 'Rubis / Saphir / Émeraude',
        );

        self::assertSame(1, $response->count);
        self::assertSame('Ruby / Sapphire / Emerald', $response->name);
        self::assertSame('Rubis / Saphir / Émeraude', $response->frenchName);
    }
}
