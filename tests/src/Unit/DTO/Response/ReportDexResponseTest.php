<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ReportDexResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportDexResponse::class)]
final class ReportDexResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new ReportDexResponse(
            name: 'Home',
            frenchName: 'Home',
        );

        self::assertSame('Home', $response->name);
        self::assertSame('Home', $response->frenchName);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportDexResponse(
            name: 'Ruby / Sapphire / Emerald',
            frenchName: 'Rubis / Saphir / Émeraude',
        );

        self::assertSame('Ruby / Sapphire / Emerald', $response->name);
        self::assertSame('Rubis / Saphir / Émeraude', $response->frenchName);
    }
}
