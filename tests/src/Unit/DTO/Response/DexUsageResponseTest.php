<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportDexResponse;
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
        $dex = new ReportDexResponse(name: 'Home', frenchName: 'Home');
        $response = new DexUsageResponse(
            count: 2,
            dex: $dex,
        );

        self::assertSame(2, $response->count);
        self::assertSame($dex, $response->dex);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $dex = new ReportDexResponse(name: 'Ruby / Sapphire / Emerald', frenchName: 'Rubis / Saphir / Émeraude');
        $response = new DexUsageResponse(
            count: 1,
            dex: $dex,
        );

        self::assertSame(1, $response->count);
        self::assertSame($dex, $response->dex);
    }
}
